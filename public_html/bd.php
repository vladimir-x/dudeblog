<?php

class DataServise {

    var $mysqli = null;

    public function DataServise() {
        $this->mysqli = $this->init();
    }

    function init() {

        if (is_null($this->mysqli)){
            $this->mysqli = new mysqli("localhost", "b91137um_blogdb", "Z7YDw1yv", "b91137um_blogdb");
            if ($this->mysqli->connect_errno) {

                printf("DB CONNECTION ERROR: %s\n", $this->mysqli->connect_error);
                exit();
            }
        }
    }

    function query($sql) {
        if (is_null($this->mysqli)){
            $this->init();
        }

        return  $this->mysqli->query($sql);
    }

    function get_row(&$query_res) {
        if (is_null($this->mysqli)){
            $this->init();
        }
        return $query_res->fetch_assoc();
    }

    function get_count($sql) {
        if (is_null($this->mysqli)){
            $this->init();
        }

        $res = -1;
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            $stmt->execute();
            $stmt->store_result();
            $res = $stmt->num_rows;
            $stmt->close();
        }
        return $res;

    }

    function save($sql) {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            if ($stmt->execute()){
                $stmt->close();
                return '{"success": true, "detail":""}';
            } else{
                $error = $stmt->error;
                $stmt->close();
                return '{"success": false, "detail":"'.$error.'"}';
            }
        }
        return  '{"success": false, "detail":"DB SAVE ERROR"}';
    }

    function auth($uname, $passwd) {
		
		$query = "SELECT * FROM users where login='" . $uname . "' and passwd='".$passwd."'";
		$row = $this->get_row($this->query($query));
		
		if (is_null($row)){
			return false;
		} else {
			$_SESSION['aUser'] = $row['title'];
			return true;
		}
    }

    function exitUser() {
        $_SESSION['auth'] = '';
        $_SESSION['aUser'] = '';
    }

    function check() {
        return $_SESSION['aUser'];
    }

    function getMessageHtmlById($id) {
        $query = "SELECT * FROM blog where id=" . $id;
		$row =  $this->get_row($this->query($query));
        return  $this->getMessageHtml($row, false);
    }
      function getMessageTitleById($id) {
        $query = "SELECT title FROM blog where id=" . $id;
		$row =  $this->get_row($this->query($query));
        return $row['title'];
    }

    function getMessageDataById($id) {
        $query = "SELECT * FROM blog where id=" . $id;
		$row =  $this->get_row($this->query($query));
        return $row;
    }

    function getMessageHtml($row, $hasLink) {
        $idStr = $row['id'];
        $titleStr = $row['title']; //mb_convert_encoding($row['title'],"UTF-8","WINDOWS-1251");
        $messStr = $row['message']; //mb_convert_encoding($row['message'],"UTF-8","WINDOWS-1251");
        $type = $row['type']; //mb_convert_encoding($row['type'],"UTF-8","WINDOWS-1251");

        $author = $row['author']; // mb_convert_encoding($row['author'],"UTF-8","WINDOWS-1251");
        $dateTime = $row['editTime'];

        $url = '?message=' . $idStr;

        $titleStr = "<a href=\"".$url."\" ><b>".$titleStr."</b></a>";
        $watch =  "<a href=\"\" onclick=\"openPopUp('" . $url . "'); return false;\">"
                . "<span class=\"glyphicon glyphicon-eye-open\" aria-hidden=\"true\"></span>"
                . "</a>";
        $edit = "<a href='?addblog=" . $idStr . "'><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span></a>";
        $del = "<a href='#' onclick=\"del(" . $idStr . ");\"><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></a>";

        $closeBt = "<a href='#' data-dismiss=\"modal\"><span class=\"glyphicon glyphicon-remove-sign\" aria-hidden=\"true\"></span></a>";
        //$closeBt = "<button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span class=\"glyphicon glyphicon-remove-sign\" aria-hidden=\"true\"></span></button>";

        $control = "<div style='float: right;'>";

        if ($hasLink) {
            $control = $control . $watch;
            if ($this->check())
                $control = $control . $edit;
            if ($this->check())
                $control = $control . $del;
        } else {
            $control = $control . $closeBt;
        }

        $control = $control . "</div>";
        $resHeader =  $titleStr . " (" . $author . " " . $dateTime . " <i>" . $type . "</i>) " . $control;


        $panClass = 'panel-default';
        switch ($type) {
            case 'Общее':
                $panClass = 'panel-warning';
                break;
            case 'Программирование':
                $panClass = 'panel-info';
                break;
            case 'Туризм':
            case 'Спорт':
            case 'Еда':
            case 'Книги':
                $panClass = 'panel-success';
                break;
            default:
                $panClass = 'panel-default';
        }


        $res = "<div class='panel " . $panClass . "'>" .
                "<div class='panel-heading'>" . $resHeader . "</div>" .
                "<div class='panel-body mess-body'>" . $messStr . "</div>" .
                "</div>";
        return $res;
    }

    function getSearchBlogHtml($findText, $limit, $offcet) {
        if ($offcet == '')
            $offcet = 0;

        $lowerFind = strtolower($findText);
        $sql = "SELECT * FROM blog " .
                "where LOWER(message) like '%" . $lowerFind . "%' " .
                "order by editTime desc limit " . $offcet . " , " . $limit . " ";


        $mesgs = '';
        $query_res = $this->query($sql);

        while ($row = $this->get_row($query_res)) {
            $mesgs = $mesgs . $this->getMessageHtml($row, true);
        }

        //подсветка
        //$upperFind = '<b>'.strtoupper($findText).'</b>';
        $mesgs = preg_replace('/(' . $findText . ')/iu', '<mark>\\1</mark>', $mesgs); //str_ireplace($findText,$upperFind,$mesgs);
        //
			$dataRaw = file_get_contents('blog.html');
        $dataRaw = str_replace(':blogPlace', $mesgs, $dataRaw);
        return $dataRaw;
    }

    function getBlogHtml($sector, $limit, $offcet) {
        switch ($sector) {
            case 'progr' : $filtV = 'Программирование';
                break;
            case 'tourism' : $filtV = 'Туризм';
                break;
            case 'sport' : $filtV = 'Спорт';
                break;
            case 'food' : $filtV = 'Еда';
                break;
            case 'books' : $filtV = 'Книги';
                break;
            default: $filtV = '';
        }

        $filtE = $filtV; //mb_convert_encoding($filtV,"WINDOWS-1251","UTF-8");

        if ($offcet == '')
            $offcet = 0;

        $sql = "SELECT * FROM blog " .
                ($filtE != '' ? "where type='" . $filtE . "' " : "" ) .
                "order by editTime desc limit " . $offcet . " , " . $limit . " ";

        $mesgs = '';

        $query_res = $this->query($sql);

        while ($row = $this->get_row($query_res)) {
            $mesgs = $mesgs . $this->getMessageHtml($row, true);
        }

        $dataRaw = file_get_contents('blog.html');
        $dataRaw = str_replace(':blogPlace', $mesgs, $dataRaw);
        return $dataRaw;
    }

    function getCount($sector) {
        switch ($sector) {
            case 'progr' : $filtV = 'Программирование';
                break;
            case 'tourism' : $filtV = 'Туризм';
                break;
            case 'sport' : $filtV = 'Спорт';
                break;
            case 'food' : $filtV = 'Еда';
                break;
            case 'books' : $filtV = 'Книги';
                break;
            default: $filtV = '';
        }

        $filtE = $filtV; //mb_convert_encoding($filtV,"WINDOWS-1251","UTF-8");
        $sql = "SELECT count(*) as cnt FROM blog " . ($filtE != '' ? "where type='" . $filtE . "' " : "" );

        return  $this->get_row($this->query($sql))["cnt"];
    }

    function add($id, $title, $message, $type, $author, $dateTime) {
        if ($this->check()) {

            $title = str_replace("'","\'",$title);
            $message = str_replace("'","\'",$message);

            if ($id > 0) {
                $updateQuery = "update blog set " .
                        "title='" . $title . "', " .
                        "message='" . $message . "', " .
                        "author='" . $author . "', " .
                        //"editTime='".$dateTime."', ".
                        "type='" . $type . "' " .
                        "where id=" . $id . " ";
                return $this->save($updateQuery);
            } else {
                $insQuery = "insert into blog(title,message,author,editTime,type) values(" .
                        "'" . $title . "'," .
                        "'" . $message . "'," .
                        "'" . $author . "'," .
                        "'" . $dateTime . "'," .
                        "'" . $type . "'" .
                        ")";

                return $this->save($insQuery);
            }
        } else {
			return '{"success": false, "detail":"Auth Fail!"}';
		}
    }

    function delMessage($id) {
        if ($this->check()) {
            $delQuery = "delete from blog where id =" . $id;
            return $this->save($delQuery);
        }
    }

}

?> 