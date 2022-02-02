<?php
/**
 * @file PrereviewDOIPlugin.inc.php
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class PrereviewDOIPlugin
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');
//TESTING
define('PREREVIEW_API_CHECK', 'https://prereview2-staging.azurewebsites.net/api/v2/preprints/');
define('PREREVIEW_API_SEND', 'https://prereview2-staging.azurewebsites.net/api/v2/resolve?identifier=');


class PrereviewDOIPlugin extends ImportExportPlugin {
	/**
	 * @copydoc ImportExportPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * @copydoc ImportExportPlugin::getName()
	 */
	public function getName() {
		return 'PREreview';
	}

	/**
	 * @copydoc ImportExportPlugin::getDisplayName()
	 */
	public function getDisplayName() {
		return "PREreview";
	}

	/**
	 * @copydoc ImportExportPlugin::getDescription()
	 */
	public function getDescription() {
		return __('plugins.importexport.prereview.description');
	}

	/**
	 * @copydoc ImportExportPlugin::register()
	 */
	public function display($args, $request) {
		parent::display($args, $request);
		
		$context = $request->getContext();

		// Get the journal or press id
		$contextId = Application::get()->getRequest()->getContext()->getId();

		// Use the path to determine which action
		// should be taken.
		$path = array_shift($args);
		switch ($path) {
			// send DOI
			case 'exportDoi':

				$this->exportDoi((array)$request->getUserVar('selectedSubmissions'));
				$path = array('plugin', $this->getName());

				$request->redirect(null, null, null, $path, null, null);
				break;

			default:

				$pubIdPlugins = PluginRegistry::loadCategory('generic', true);
				if (isset($pubIdPlugins['prereviewplugin'])) {
					$application = Application::get();
					$request = $application->getRequest();
					$dispatcher = $application->getDispatcher();
					import('lib.pkp.classes.linkAction.request.AjaxModal');
					$prereviewSettingsLinkAction = new LinkAction(
						'settings',
						new AjaxModal(
							$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.settings.plugins.SettingsPluginGridHandler', 'manage', null, array(
								'verb' => 'settings',
								'plugin' => 'prereviewplugin',
								'category' => 'generic'
							)),
							__("plugins.importexport.prereview.settings")
						),
						__('plugins.importexport.prereview.settings'),
						null
					);
				}
				$plugin = PluginRegistry::getPlugin('generic', 'prereviewplugin'); /* @var $plugin PREreview */
				if($plugin)
				{
					$api=$plugin->getSetting($context->getId(), 'prereviewApp');
					$key=$plugin->getSetting($context->getId(), 'prereviewkey');
					if($api && $key)
					{ $plugin='true';}else{$plugin='false';}
				}else{$plugin='false';}
					$data_prereview = $this->getAllData($contextId);					
					$result=[];
					$i=0;				
					
					foreach ($data_prereview as $data_p){
						if(!empty($data_p->doi) && !empty($data_p->prereview) && $data_p->prereview!="no")
						{
						$submission = \Services::get('submission')->get($data_p->id);
						$getPropertiesArgs = [
							'request' => $request,
						];
						$submissionProps = \Services::get('submission')->getProperties(
							$submission,
							[
								'urlWorkflow',
							],
							$getPropertiesArgs
						);
							$status="Not Deposited";
							if($data_p->prereview_status == 'EXPORT_STATUS_REGISTERED'){
								$status= __('plugins.importexport.prereview.status.registered');
							} 
							$result[$data_p->id]=array(
								'id' => $data_p->id,
								'name' => $getTitle = $this->getTitle($data_p->id),
								'doi'  => $data_p->doi,
								'request' => $data_p->prereview,
								'status' => $status ,
								'url' => $submissionProps['urlWorkflow'],
							);	
						}
					}
				
					$templateMgr = TemplateManager::getManager($request);
						
				
						$templateMgr->assign([
							'pageTitle' => 'PREreview',
							'pageComponent' => 'ImportExportPage',
							'publications' => $result,
							'prereviewSettingsLinkAction' => $prereviewSettingsLinkAction,					
							'plugin' => $plugin,
							'publicationAll' => $data_prereview
						]);
						$templateMgr->display($this->getTemplateResource('export.tpl'));
		}
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI()
	 */
	public function executeCLI($scriptName, &$args) {
		$csvFile = array_shift($args);
		$contextId = array_shift($args);
	}

	/**
	 * @copydoc ImportExportPlugin::usage()
	 */
	public function usage($scriptName) {
		echo __('plugins.importexport.exampleImportExport.cliUsage', array(
			'scriptName' => $scriptName,
			'pluginName' => $this->getName()
		)) . "\n";
	}

	/**
	 *
	 * @param	int	$contextId Which journal or press to get submissions for
	 * @return DAOResultIterator
	 */
	public function getAll($contextId) {
		import('classes.submission.Submission');
		$submisions= Services::get('submission')->getMany([
			'contextId' => $contextId,
			'status' => STATUS_PUBLISHED,
		]);
		return $submisions;
	}
	public function getTitle($id) {
		import('classes.submission.Submission');
		$submission = Services::get('submission')->get($id);
		$submission = $submission->getLocalizedTitle();
		return $submission;
	} 
	
	public function getDoi($id) {
		import('classes.submission.Submission');
		$submission = Services::get('submission')->get($id);
		$submission = $submission->getData('publications')[0]->getData('pub-id::doi');
		return $submission;
	} 

	public function getDataPrereview($id)
	{
		$this->import('PrereviewExportPluginDAO');
		$prereview = new PrereviewExportPluginDAO();
		DAORegistry::registerDAO('PrereviewExportPluginDAO', $prereview);
		$preDao = DAORegistry::getDAO('PrereviewExportPluginDAO'); 	
		$result= $preDao->getDataPrereview($id);
		return $result;
		
	}
	public function getAllData($contextId)
	{
		$this->import('PrereviewExportPluginDAO');
		$prereview = new PrereviewExportPluginDAO();
		DAORegistry::registerDAO('PrereviewExportPluginDAO', $prereview);
		$preDao = DAORegistry::getDAO('PrereviewExportPluginDAO'); 	
		$result= $preDao->getAllData($contextId);
		return $result;
		
	}

	/**
	 * @param DAOResultIterator 
	 * @param string $filename
	 */

	public function exportDoi($submisions){
		$application = Application::get();
		$request = $application->getRequest();
		$contextId = Application::get()->getRequest()->getContext()->getId();
		$plugin = PluginRegistry::getPlugin('generic', 'prereviewplugin'); /* @var $plugin PREreview */
			$api=$plugin->getSetting($contextId, 'prereviewApp');
			$key=$plugin->getSetting($contextId, 'prereviewkey');
		foreach($submisions as $subm)
		{
			$id=((int) $subm);
			$urldoi = $this->getDoi($id);
			settype($urldoi, 'string');
			if($this->validateDoi($urldoi)=="ok"){
				$reply = $this->sendDoi($urldoi, $api, $key, $request);
				$this->depositStatus($reply, $id);

			 }elseif($this->checkDoi($urldoi) == 'ok'){
				$reply = $this->sendDoi($urldoi, $api, $key, $request);
				$this->depositStatus($reply, $id);
			 }else{
				$this->notification(
					NOTIFICATION_TYPE_ERROR,
					'plugins.importexport.prereview.send.error.mdsError'
				);
			}
			
		}
	}
	function checkDoi($urldoi){
		$doi="doi-".str_replace("/", "-", strtolower($urldoi));
		$url = PREREVIEW_API_CHECK.$doi;
		$res = file_get_contents($url);
		$res = json_decode($res);
		$data= $res->status;
		return $data;
	}

	function validateDoi($doi){
		$url = PREREVIEW_API_SEND.$doi;
		$res = file_get_contents($url);
		if($res){
			return "ok";
		}
	}
	function sendDoi($urldoi, $apiname, $key, $request){
		$status='';
		$doi="doi-".str_replace("/", "-", strtolower($urldoi));
        $api=PREREVIEW_API_CHECK . $doi . "/requests";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);                                                                   
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);                                                                
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Length: 0',
            'Content-Type: application/json',
			'Accept: application/json',
			'X-API-App:'.$apiname,
			'X-API-Key:'.$key,)                                                                       
        );                                                                                                                   
                                                                                                                            
        $result = curl_exec($ch);
		$result = json_decode($result);

        if ($result->message === "created") 
        {
			$status = EXPORT_STATUS_REGISTERED;

			$this->notification(
				NOTIFICATION_TYPE_SUCCESS,
				'plugins.importexport.prereview.configure.success.mdSuccess',
			);
        }elseif($result->message === "Request already exists") {
			$this->notification(
				NOTIFICATION_TYPE_ERROR,
				'plugins.importexport.prereview.request.error.mdsError',
			);
		}
		else{
			
			$this->notification(
				NOTIFICATION_TYPE_ERROR,
				'plugins.importexport.prereview.configure.error.mdsError',
			);
		}
       

        curl_close($ch);

		return $status;

	}

	function notification($type, $message)
	{
		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification(
			Application::get()->getRequest()->getUser()->getId(),
			$type,
			['contents' => __($message)]
			);	
	}
	function depositStatus($reply, $id){
		if($reply == EXPORT_STATUS_REGISTERED){
			$this->import('PrereviewExportPluginDAO');
			$prereview = new PrereviewExportPluginDAO();
			DAORegistry::registerDAO('PrereviewExportPluginDAO', $prereview);
			$preDao = DAORegistry::getDAO('PrereviewExportPluginDAO'); 
			$data = $preDao->updateStatus('EXPORT_STATUS_REGISTERED', $id);
		}

	}

	
}
