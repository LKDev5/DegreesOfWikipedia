<?php
    //curl.class.php
    //takes the functionality of curl and wraps it into a nice class!
    
    //MAKE SURE TO ALLOW LONG FILE PATH NAMES  TYPE THIS FROM THE COMMAND PROMPT:
    //fsutil.exe behavior set disable8dot3 1
    
    global $cachefilepath;
	$cachefilepath = dirname(__FILE__) . "/cache/";
    
    
    
    class curlclass
    {

        var $enable_read_cache;
        var $enable_write_cache;
        
        var $setopt = array();
        
        
        function curlclass()
        {

            $this->enable_read_cache    = false;
            $this->enable_write_cache   = false;
            
            $this->setopt[CURLOPT_USERAGENT] = 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11 )';
            $this->setopt[CURLOPT_COOKIESESSION] = true;    //use cookies
            $this->setopt[CURLOPT_RETURNTRANSFER] = true;    //return the transfer
        }
        
        function setMethod($method = 'get')
        {
            $method = trim(strtolower($method));
            if($method == 'post')
            {
                $this->setopt[CURLOPT_POST] = true; //POST method
            }
            else
            {
                $this->setopt[CURLOPT_POST] = false;    //GET method
            }
        }
        
        function setURL($url)
        {
            $this->setopt[CURLOPT_URL] = $url;
        }
        
        //values for post in $key=>$value format
        function setPostValues($postvalues,$urlencodekey=false,$urlencodevalue=false)
        {
            $post_pieces = array();
            if(count($postvalues) > 0)
            {
                foreach($postvalues as $key=>$value)
                {
                    
                    if($urlencodekey)
                    {
                        $key = urlencode($key);
                    }
                    if($urlencodevalue)
                    {
                        $value = urlencode($value);
                    }
                    
                    $post_pieces[] = "$key=$value";
                }
            }
            if(count($post_pieces) > 0)
            {
                $this->setopt[CURLOPT_POSTFIELDS] = join('&',$post_pieces);
            }
            
        }
        
        //simplified version of setPostValues, if you have already built the string
        function setPostString($poststring)
        {
            $this->setopt[CURLOPT_POSTFIELDS] = $poststring;
        }
        
        
        //Setting additional parameters for the CURL object
        function setAdditionalParameters($curl_extra_params)
        {
            //set the additional options that were passed in, if there are any, and possibly override the local defaults
            if(count($curl_extra_params) > 0)
            {
                foreach($curl_extra_params as $key=>$val)
                {
                    //echo "setting $key to $val\n";
                    $this->setopt[$key] = $val;
                }
            }
        }
        
        
        function calcHash()
        {
            ksort($this->setopt);   //try to account for keys being out of order
            return md5(serialize($this->setopt));
        }
        

        
        
        //fetch the page and return the result
        function exec()
        {                        
            $hash = $this->calcHash();
            $cache_subdir = substr($hash,0,2);
            
            $cache_dir = $GLOBALS['cachefilepath'] . '/' . $cache_subdir;
            
            //create the subdirectory in the cache folder
            if(!file_exists($cache_dir) && ($this->enable_read_cache || $this->enable_write_cache))
            {
                mkdir($cache_dir,null,true);
            }
            $hashedfilepath =  $cache_dir . '/' . $hash;
            
            
            if($this->enable_read_cache && file_exists($hashedfilepath) && filesize($hashedfilepath) > 0)
            {
                echo "Loaded from cache.\n";
                $data = file_get_contents($hashedfilepath);
            }
            else
            {        
                //execute the request!
                
                $ch = curl_init();
                curl_setopt_array($ch, $this->setopt);  //set all of the options stored in $this->setopt
                $data = curl_exec($ch);

                //save to cache if necessessary
                if($this->enable_write_cache)
                {
                    echo "Wrote to cache.\n";
                    file_put_contents($hashedfilepath,$data);
                }
            }
            
            //return the data
            return $data;
        }
        
        
        //extract form field values
        function extractFormFieldValues($html)
        {
            $input_fields = array();
            preg_match_all('/<input .+>/ismU',$html,$input_fields_matches);
            
            foreach($input_fields_matches[0] as $input_field_html)
            {
                //name
                $temp_name = "";
                preg_match('/name="(.+)"/U',$input_field_html,$temp_matches);
                $temp_name = $temp_matches[1];
                //value
                $temp_value = "";
                preg_match('/value="(.+)"/U',$input_field_html,$temp_matches);
                $temp_value = $temp_matches[1];
                
                if($temp_name != "")
                {
                    $input_fields[$temp_name] = $temp_value;
                }
            }
            
            return($input_fields);
        } 
        
    }
    
?>