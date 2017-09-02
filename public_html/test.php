<?php



print("test begin\n");


print("get_count()\n".get_count("select * from blog"));

print("\n");

print ("get_row():\n".get_row(query("select * from blog")));

print("test end\n");


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

?>