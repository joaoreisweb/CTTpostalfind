# CTTpostalfind
Class to search Portuguese Zip Code

# Easy to use
```
require_once('class.ctt.php');

$findctt = new CTTpostalfind();

$findctt->setCodpos('4000-407');

$result = $findctt->search('all', false);

echo "<br>".$result['localidade']['designacao']
echo "<br>".$result['localidade']['freguesia']
```
