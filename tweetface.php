<?php

/**
 * PHP class to interface with twitter API as simply as possible.
 *
 * For twitter API documentation:
 * @see https://dev.twitter.com/rest/public
 *
 * This class requires TwitterOauth
 * @see https://github.com/abraham/twitteroauth
 *
 * @author N1ghteyes - www.source-control.co.uk
 * @copyright 2016 N1ghteyes
 * @license license.txt The MIT License (MIT)
 * @link https://github.com/N1ghteyes/TweetFace
 */
require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

class tweetface
{
    private $t; //TwitterOauth object
    private $media = []; //Media array - for adding multiple files.
    private $request = []; //Request being built before Sending
    private $log = []; //Store the twitter returns from each function call. This is primarily for debug and full returns.

    private $type = 'get'; //set the request type
    private $scope = 'statuses'; //set the api scope

    /**
     * Initial constructor to load the twitterOauth class.
     * @param $consumer_key
     * @param $consumer_secret
     * @param $access_token
     * @param $access_token_secret
     */
    public function __construct($consumer_key, $consumer_secret, $access_token, $access_token_secret){
        $this->t = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        return $this;
    }

    /**
     * Short hand tweet function
     * @param $status
     * @return mixed
     */
    public function tweet($status){
        return $this->log[__FUNCTION__] = $this->post->statuses->update(['status' => $status]); //call self, set type and scope.
    }

    /**
     * Function to return the log for a specific function, or the entire call chain.
     * @param string $function
     * @return array
     */
    public function getLog($function = ""){
        return isset($function) ? $this->log[$function] : $this->log;
    }

    /**
     * Uses __get() to (ironically) set the connection type and scope.
     * @param $name
     * @return $this
     */
    public function __get($name){
        switch(strtolower($name)){
            case "get":
                $this->setType('get');
                break;
            case "post":
                $this->setType('post');
                break;
            default:
                $this->setScope($name);
                break;
        }
        return $this;
    }

    /**
     * Magic php __call() function to handle standard api requests.
     * NOTE: we do something a little different to handle media uploads.
     * Passing both 'path' and 'status' through arguments will tweet the attached media immediately.
     * @param $name
     * @param $arguments
     * @return Mixed
     */
    public function __call($name, $arguments){
        $this->_buildInitialRequest($arguments);
        $response = array();
        switch(strtolower($name)){
            case 'upload': //We have to handle media carefully, so it has its own thing here.
            case 'media': //allow for a media call, even though it should be upload.
                if($this->scope == 'media' && !empty($this->request)){
                    $this->addMedia($this->request);
                    if(isset($this->request['status'])) {
                        $this->log[__FUNCTION__.'('.$name.')'] = $response = $this->post->statuses->update(); //call self, set type and scope.
                    } else {
                        return $this; //allow chaining for media
                    }
                }
                break;
            default:
                $this->log[__FUNCTION__.'('.$name.')'] = $response = $this->t->{$this->type}($this->scope.'/'.$name, $this->request);
                break;
        }
        return $response;
    }

    private function setType($type){
        $this->type = $type;
        return $this;
    }

    private function setScope($name){
        $this->scope = $name;
        return $this;
    }

    private function _buildInitialRequest($args){
        if(is_array($args) && !empty($args)) {
            foreach ($args as $inner) {
                if(is_array($inner)) {
                    $this->request += $inner;
                } else {
                    //Assume string or object, if the former right now we only support media. If the latter, typecast
                    $this->request += $this->scope == 'media' ? ['media' => $inner] : (array)$inner;
                }
            }
        }
    }

    /**
     * Handle initial media upload - Allow for chaining.
     * @param $request
     * @return $this
     */
    private function addMedia($request){
        $this->log[__FUNCTION__] = $mid = $this->t->upload('media/upload', $request);
        $this->media[] = $mid->media_id_string;
        $this->request['media_ids'] = implode(',',$this->media);
        return $this;
    }
}