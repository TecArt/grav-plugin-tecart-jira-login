<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\User\User;
use Grav\Plugin\Login\Events\UserLoginEvent;
use Grav\Plugin\Login\Login;

class TecartJiraLoginPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(){
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 10]
            ],
            'onUserLoginAuthenticate'   => ['userLoginAuthenticate', 1000],
            'onUserLoginFailure'        => ['userLoginFailure', 0],
            'onUserLogin'               => ['userLogin', 0],
            'onUserLogout'              => ['userLogout', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0]
        ];
    }

    public function onPluginsInitialized(){

        // Check to ensure admin plugin is enabled.
        if (!$this->grav['config']->get('plugins.admin.enabled')) {
            throw new \RuntimeException('The Admin plugin needs to be installed and enabled');
        }
        // Check to ensure login plugin is enabled.
        if (!$this->grav['config']->get('plugins.login.enabled')) {
            throw new \RuntimeException('The Login plugin needs to be installed and enabled');
        }
    }

    public function onAdminTwigTemplatePaths($event) {
        $paths = $event['paths'];
        $paths[] = __DIR__ . '/admin/themes/grav/templates';
        $event['paths'] = $paths;
    }

    public function userLoginAuthenticate(UserLoginEvent $event){

        // event is called when login button is pressed
        // credentials contain username and password in array Array ( [username] => [password] => )
        $credentials = $event->getCredentials();

        // if empty username -> ignore
        if($credentials['username'] == '' or $credentials['password'] == ''){
            $event->setStatus($event::AUTHENTICATION_FAILURE);
            return;
        }

        $username = $credentials['username'];
        $password = $credentials['password'];

        // Plugin parameters
        $jira_url           = $this->config->get('plugins.tecart-jira-login.jira_url');
        $jira_api           = $this->config->get('plugins.tecart-jira-login.jira_api') ?: 'rest/api/2/issue/createmeta';
        $save_as_account    = $this->config->get('plugins.tecart-jira-login.save_as_account');

        if (is_null($jira_url)) {
            throw new ConnectionException('FATAL: Jira URL entry missing in plugin configuration.');
        }

        // set cURL url - https://jira.tecart.de/rest/api/2/issue/createmeta
        $ch_url = $jira_url.'/'.$jira_api;

        // do the Jira Basic Authentification
        $request = $this->jiraBasicAuthentication($ch_url, $username, $password);

        // if cURL error
        if (isset($request['error'])) {

            $this->grav['log']->error('plugin.tecart-jira-login: ' .  $username . ' - ' . $request['error']);

            // Just return so other authenticators can take a shot...
            return;
        }

        // if authentication success
       if(isset($request['http_code'])  &&  $request['http_code'] <= 299){
            // Create Grav User (no saving at this point - just serve user infos to make grav admin work)
            $grav_user = $this->setGravUser($username);

            // Save user
            if ($save_as_account === true) {
                $grav_user->save();
            }

            // Login
            $event->setUser($grav_user);

            $event->setStatus($event::AUTHENTICATION_SUCCESS);
            $event->stopPropagation();
        }
        // if authentication error
        else{
            $event->setStatus($event::AUTHENTICATION_FAILURE);
            $event->stopPropagation();
        }
    }

    public function userLoginFailure(UserLoginEvent $event){
        // This gets fired if user fails to log in.
    }

    public function userLogin(UserLoginEvent $event){
        // This gets fired if user successfully logs in.
    }

    public function userLogout(UserLoginEvent $event){
        // This gets fired on user logout.
    }

    //https://developer.atlassian.com/server/jira/platform/basic-authentication/
    protected function jiraBasicAuthentication($url="", $user="", $password=""){

        $ch = curl_init();

        $token = base64_encode($user.":".$password);

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.$token
        );

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($ch, CURLOPT_USERPWD, $user.":".$password);

        $curl = curl_exec($ch);
        //print_r($curl);

        $ch_error = curl_error($ch);

        if ($ch_error) {
            $result = array('error' => 'cURL Error: '.$ch_error);
        }
        else{
            $result = curl_getinfo($ch);
        }

        curl_close($ch);

        return $result;
    }

    protected function setGravUser($username) {

        // Create Grav User
        $grav_user = User::load(strtolower($username));

        // Plugin Settings
        $admins             = $this->config->get('plugins.tecart-jira-login.admin_users');
        $admin_language     = $this->config->get('plugins.tecart-jira-login.admin_language') ?: 'de';

        // Set permissions
        $permissions = array();

        $permissions['site']['login'] = true;
        $permissions['admin']['login'] = true;
        $permissions['admin']['pages'] = true;
        $permissions['admin']['cache'] = true;
        $permissions['admin']['configuration_site'] = true;

        // usernames that should act as admin, remove whitespaces
        $admins_array =explode(',',preg_replace('/\s+/', '', $admins));

        // if username is admin
        if(in_array($username, $admins_array)){
            $permissions['admin']['super'] = true;
            $permissions['admin']['configuration'] = true;
            $permissions['admin']['maintenance'] = true;
            $permissions['admin']['statistics'] = true;
            $permissions['admin']['plugins'] = true;
            $permissions['admin']['themes'] = true;
            $permissions['admin']['tools'] = true;
            $permissions['admin']['users'] = true;
            $permissions['admin']['flex-objects'] = true;
        }

        // Set user
        //$grav_user['fullname']  = 'Jira User';
        $grav_user['fullname']  = $username;        //is shown in admin panel
        $grav_user['email']     = 'jira@tecart.de';
        $grav_user['language']  = $admin_language;
        $grav_user['access']    = $permissions;

        return $grav_user;
    }
}
