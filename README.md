# TweetFace
------
Wrapper for the twitterOauth php class - uses __get() and __call() to provide an intelegent object interface.

## Examples
------
  Load the object  
  ```php
  $t = new tweetface(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
  ```

### Normal implimentation
  using twitter oath this would be: 
  ```php
  //$t->get("statuses/home_timeline", ["count" => 25, "exclude_replies" => true]);
  $response = $t->get->statuses->home_timeline(["count" => 25, "exclude_replies" => true]);
  ```

### multiple requests within scope
  Get the last 20 retweets of me, after home_timeline has set type to get and scope to statuses  
  ```php
  $response = $t->retweets_of_me();
  ```

  Change scope, keep the get request - direct_messages  
  ```php
  $response = $t->direct_messages->show(["id" => 587424932]);
  ```
  
  Keep scope, change the request  
  ```php
  $t->post->new(['text' => 'Hello!', 'screen_name' => "source_control"]);
  ```
  
  Keep scope and request 
  ```php
  $t->destroy(["id" => 587424932]);
  ```

### Quick tweet
  this resets the request and scope to post statuses  
  ```php
  $t->tweet('My Tweet Text');
  ```

### Media Tweets
  Simple - By default passes media as raw binary - tweet in a chain.  
  ```php
  $t->media($filepath)->tweet('My Media');
  ```

  Pass multiple media items - chaining!  
  ```php
  $t->media($filepath)->media($filepath2)->media($filepath3)->tweet('My IMages');
  ```
  
  Pass argument arrays, and tweet immidiatly.  
  ```php
  $t->media(['media' => $filepath, 'status' => 'My Tweet']);
  ```
  
  Pass different parms (i.e. base64 image) - and tweet  
  ```php
  $params = ['media_data' => $encoded_image, 'status' => 'My base64 image'];
  $t->media($params);
  ```
