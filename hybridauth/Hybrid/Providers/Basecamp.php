<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2012 HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

class Hybrid_Providers_Basecamp extends Hybrid_Provider_Model_OAuth2
{
    // default permissions
    // public $scope = "basic";
    protected $user;
    /**
     * IDp wrappers initializer
     */
    function initialize()
    {
        parent::initialize();

        // Provider api end-points
        $this->api->api_base_url  = $this->user->account->href;
        $this->api->authorize_url = "https://launchpad.37signals.com/authorization/new";
        $this->api->token_url     = "https://launchpad.37signals.com/authorization/token";
    }

    /**
     * load the user profile from the IDp api client
     */
    function getUserProfile(){
        $data = $this->api->api("me");

        //if ( $data->meta->code != 200 ){
        //  throw new Exception( "User profile request failed! {$this->providerId} returned an invalid response.", 6 );
        //}

        $this->user->profile->identifier  = $data->identity->id;
        $this->user->profile->email = $data->identity->email;
        $this->user->profile->displayName = $data->identity->first_name;
        $this->user->profile->firstName = $data->identity->first_name;
        $this->user->profile->lastName = $data->identity->last_name;

        return $this->user->profile;
    }
    function getUserAccount() {
        $data = $this->api->api("me");

        $this->user->account->id = $data->accounts->id;
        $this->user->account->name = $data->accounts->name;
        $this->user->account->product = $data->accounts->product;
        $this->user->account->href = $data->accounts->href;
        $this->user->account->app_href = $data->accounts->app_href;

        return $this->user->account;
    }
}