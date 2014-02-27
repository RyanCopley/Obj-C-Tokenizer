<?php
function tokenize($contents){
	//Normalize Newlines
	$contents=str_replace("\r\n","\n",$contents);
	$contents=str_replace("\r","\n",$contents);
	$contents = explode("\n",$contents);
	
	//Create some variables to hold our data / temp data
	$freshContents="";
	$comments=array();
	$strings=array();
	$blockComments=array();
	$preProcessor=array();
	$singleQuotes=array();
	
	foreach ($contents as $key=>$line){
		//Some quick trimming
		$tmp=trim($line);
		
		//Preprocessor Censor
		if ($tmp[0] == "#"){
			$preProcessor[] = $tmp;
			$tmp = chr(1);
		}else{ //... Everything else
			if (strpos($tmp,"//") !== FALSE){
				$comments[] = substr($tmp,strpos($tmp,"//"),strlen($tmp));
				$tmp = substr($tmp,0,strpos($tmp,"//")).chr(2);	
			}
			$tmp=trim($tmp);
			
			//Escaped Quote Censor
			$tmp=str_replace('\"',chr(3), $tmp);
			
			//Double Quote Censor
			while (strpos($tmp,"\"") !== FALSE && substr_count($tmp,"\"") % 2 == 0){
				$preQuote=substr($tmp,0,strpos($tmp,"\"")); // Before the quote
				$_tmp=substr($tmp,strpos($tmp,"\"")+1,strlen($tmp)); //quote+1+tail
				$quote=substr($_tmp,0,strpos($_tmp,"\""));
				$afterQuote=substr($_tmp,strpos($_tmp,"\"")+1,strlen($_tmp)); //quote+1+tail
				$strings[]=$quote;
				$tmp = $preQuote.chr(4).$afterQuote;
			}	
			
			//Single Quote Censor
			while (strpos($tmp,"'") !== FALSE && substr_count($tmp,"'") % 2 == 0){
				$preQuote=substr($tmp,0,strpos($tmp,"'")); // Before the quote
				$_tmp=substr($tmp,strpos($tmp,"'")+1,strlen($tmp)); //quote+1+tail
				$quote=substr($_tmp,0,strpos($_tmp,"'"));
				$afterQuote=substr($_tmp,strpos($_tmp,"'")+1,strlen($_tmp)); //quote+1+tail
				$singleQuotes[]=$quote;
				$tmp = $preQuote.chr(5).$afterQuote;
			}	
			
			
		}		
		if ($tmp != ""){
			$freshContents.=$tmp."\n";
		}
	}
	
	while (strpos($freshContents,"/*") !== FALSE){
			$preBlockComment=substr($freshContents,0,strpos($freshContents,"/*"));
			$_tmp=substr($freshContents,strpos($freshContents,"/*")+2,strlen($freshContents));
			$blockComment=trim(substr($_tmp,0,strpos($_tmp,"*/")));
			$afterBlockComment=substr($_tmp,strpos($_tmp,"*/")+2,strlen($_tmp));
			$blockComments[]=$blockComment;
			$freshContents = $preBlockComment.chr(6).$afterBlockComment;
	}
	
	// Tidy up pre-processor statements
	
	
	//Lets start exploding based on some delimters now that we have a safe code set
	
	//Censor multi character operators so we don't break them down too far
	$additionalCensors = array(
								"!="=>chr(7),
								"=="=>chr(8),
								"&&"=>chr(9),
								"||"=>chr(11),
								"<<"=>chr(12),
								">>"=>chr(14),
								"->"=>chr(15),
								"--"=>chr(16),
								"++"=>chr(17)
							  );
	foreach ($additionalCensors as $search=>$replacement){
		$freshContents=str_replace($search,$replacement,$freshContents);
	}
	
	//Tear down to as small as I can
	$explodeChars = array_merge(array(";","[","]","(",")","{","}",","," ",":","!",">","<","@","+","-","*","/","&","^","?",".","%"), array_values($additionalCensors));
	foreach ($explodeChars as $char){
		$freshContents=str_replace($char,"\n".$char."\n",$freshContents);
	}
	
	$dirtyNodes = explode("\n",$freshContents);
	$cleanNodes = array();
	foreach ($dirtyNodes as $key=>$line){
		$line = trim($line);
		if ($line != ""){
			$cleanNodes[] = $line;
		}
	}
	
	return array(
		"tokens"=>$cleanNodes,
		"comments"=>$comments,
		"blockComments"=>$blockComments,
		"strings"=>$strings,
		"singleQuotes"=>$singleQuotes,
		"preProcessor"=>$preProcessor
		);
}
?>