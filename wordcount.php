<?php

class word{
	
	public $word;

    public function __construct(){
		global $conn;
        $this->conn = $conn;
    }
	
	public function processword($word){		
		$this->word = $word;
		if(!$this->wordexist()){
			$this->wordsave();
		}
		$this->watchlist();
	}
	
	private function wordexist(){
		$sql = "SELECT * FROM `distinct_unique_words` WHERE `word` LIKE '".$this->word."' LIMIT 1";
		return $this->conn->query($sql)->num_rows;
	}
	
	private function wordsave(){
		global $uniquewords;
		$sql = "INSERT INTO `distinct_unique_words` (`id`, `word`) VALUES (NULL, '".$this->word."')";
		if($this->conn->query($sql)){
			$uniquewords++;
		}
	}
	
	private function watchlist(){
		global $watchlist, $watchlist_words;
		if(!empty($watchlist) && $key = array_search($this->word, $watchlist))
		{
			unset($watchlist[$key]);
			array_push($watchlist_words, $this->word);
		}
	}
}


// Database connection
$conn = new mysqli("localhost", "root", "", "oritsys_test");

$watchlist = array();
$watchlist_words = array();
$sql = "SELECT * FROM `watchlist`";
$result = $conn->query($sql);
while($row = mysqli_fetch_assoc($result)) {
	$watchlist[$row["id"]] = $row["word"];
}

// Read file
$fp = fopen("sample.txt", "r");

$uniquewords = 0;
$wordbuffer = "";

while ($c = fgetc($fp)){
  if (preg_match('/[\W\s]+/', $c)){
	if($wordbuffer != ""){
		$handle = new word;
		$handle->processword($wordbuffer);
		$wordbuffer = "";
	}
  } else {
	$wordbuffer.= $c;
  }
}

fclose($fp);

$conn->close();

echo '<br/>Distinct unique words: '.$uniquewords;

echo '<br/><br/>Watchlist words:';
if(!empty($watchlist_words)){
	foreach($watchlist_words as $item){
		echo '<br/>'.$item;
	}
}else{
	echo ' None';
}

?>