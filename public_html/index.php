<?php

$page = $_GET['page'];

function includeAndReplace($filename, $search, $replace) {
    $dataRaw = file_get_contents($filename);
    $dataRaw = str_replace($search, $replace, $dataRaw);
    print($dataRaw);
}

session_start();

include 'bd.php';

$carcas = file_get_contents('carcas.html');
$title = '';
$meta = '';
$content = '';
$script = '';

if ($_POST['auth']) {
    $ds = new DataServise();
    $ds->init();

    $re = $ds->auth($_POST['uname'], $_POST['passwd']);
    if ($re) {
        $uname = $_POST['uname'];
        $_SESSION['aUser'] = $uname;
        $_cookie['auser'] = $uname;
        print('true');
    } else
        print('false');
    // print('uname='.$_POST['uname'].'  passwd='.$_POST['passwd'].'   session_auser='.$_SESSION['aUser'].'  res='.$re);
    //$page = 'blog';
    exit();
}


if ($_GET['exit']) {
    $_SESSION['aUser'] = null;
    exit("Exit complete");
}

if ($_GET['check']) {
	if (is_null($_SESSION['aUser'] )){
		exit('{"success": false}');
	} else {
		exit('{"success": true, "username":"'.$_SESSION['aUser'].'"}');
	}
}

if ($_GET['del']) {
    $ds = new DataServise();
    $ds->init();
    $res  =  $ds->delMessage($_GET['id']);
	
    exit("Result 'del':" . $res);
}

if ($_POST['send']) {
    $ds = new DataServise();
    $ds->init();
    $dateTime = date('Y-m-d H:i:s', strtotime('8 hour'));
    $res = $ds->add($_POST['id'], $_POST['title'], $_POST['message'], $_POST['type'], $_SESSION['aUser'], $dateTime);
	
    exit($res);
}

if ($_GET['getServerImgs']) {
    $imgTemplate = file_get_contents('imgItem.htm');
    
    
    foreach (scandir('img') as $imgName) {
        if ($imgName !='.' && $imgName !='..' ){
        $img = $imgTemplate;
        $img = str_replace(':itemAlt', $imgName, $img);
        $img = str_replace(':itemImg', 'img/'.$imgName, $img);
        
        print($img);
        }
    } 

    /*
    $img1 = $imgTemplate;
    $img1 = str_replace(':itemAlt', 'wowImg', $img1);
    $img1 = str_replace(':itemImg', 'img/pie.jpg', $img1);
    */
    //:itemAlt
    //:itemImg
    //print($img1);
    //print($img2);
    exit();
}

if ($_GET['message']) {

    $ds = new DataServise();

    $title = 'cheblog: '.$ds->getMessageTitleById($_GET['message']);
    $meta = 'Сообщение: '.$ds->getMessageTitleById($_GET['message']);
    $content = $ds->getMessageHtmlById($_GET['message']);
    $script = "";
    

    if ($_GET['popup']) {
        print $content;
        exit();
    }
} else
if ($_GET['addblog']) {

    $title = 'Новый пост';
    $meta = 'Добавить новый пост';
    $content = file_get_contents('addblog.html');
    $script = "activate('" . $page . "-li" . "')";
    if ($_GET['addblog'] != 'new') {
        $ds = new DataServise();

        $md = $ds->getMessageDataById($_GET['addblog']);
        /*
          var_dump($md['id']);
          exit(); */

        $content = str_replace(":messId", $md['id'], $content);
        $content = str_replace(":messType", $md['type'], $content);
        $content = str_replace(":messText", $md['message'], $content);
        $content = str_replace(":title", $md['title'], $content);
    } else {
        $content = str_replace(":messId", 0, $content);
        $content = str_replace(":messText", "<p>_текст_</p>", $content);
        $content = str_replace(":title", "", $content);
    }
} else
    switch ($page) {

        case 'samples':
            $title = 'Примеры работ';
            $meta = 'Примеры работ';
            $content = file_get_contents('samples.html');
            $script = "activate('" . $page . "-li" . "')";
            break;
        case 'about':
            $title = 'О блоге';
            $meta = 'О блоге';
            $content = file_get_contents('about.html');
            $script = "activate('" . $page . "-li" . "')";
            break;


        case 'blog':
        case 'progr':
        case 'tourism':
        case 'sport':
        case 'food':
        case 'books':
        case 'search':
        default:

            $limit = 10;
            $offcet = $_GET['offcet'];

            $title = 'Блог';
            $meta = 'Блог разработчика';

            $script = '';
            $navPage = $page;

            $ds = new DataServise();

            if ($page == 'search') {
                $content = $ds->getSearchBlogHtml($_GET['ftext'], $limit, $offcet);
                $script = $script . " $('#findField').val('" . $_GET['ftext'] . "'); ";
                $navPage = $page . "&ftext=" . $_GET['ftext'];
            } else {
                $content = $ds->getBlogHtml($page, $limit, $offcet);
            }

            $count = $ds->getCount($page);

            $script = $script . "activate('" . $page . "-li" . "'); ";
            $script = $script . " enablePrev('" . ($offcet > 0) . "'); ";
            $script = $script . " enableNext('" . ($offcet + $limit < $count) . "'); ";

            $content = str_replace(':prevLink', '?page=' . $navPage . '&offcet=' . ($offcet - $limit), $content);
            $content = str_replace(':nextLink', '?page=' . $navPage . '&offcet=' . ($offcet + $limit), $content);

            break;
        /*
          default:
          print('$_GET debugs');
          var_dump($_GET);

          print('$_POST debugs');
          var_dump($_POST);

          print('$$_SESSION debugs');
          var_dump($_SESSION);   //['content']

          exit();

          break; */
    }

$carcas = str_replace(':title', $title, $carcas);
$carcas = str_replace(':meta', $meta, $carcas);
$carcas = str_replace(':content', $content, $carcas);
$carcas = str_replace(':script', $script, $carcas);

print($carcas);
?>