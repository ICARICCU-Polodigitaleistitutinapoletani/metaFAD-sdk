<?php
class metafad_common_controllers_rest_GetJson extends pinax_rest_core_CommandRest
{
  function execute($type)
  {
	  if(__Request::get('module') == 'archive')
	  {
		  echo file_get_contents('application/classes/userModules/archivi/json/'.$type.'.json');
	  }
	  else
	  {
		  echo file_get_contents('application/classes/userModules/'.$type.'/models/elements.json');
	  }
	  exit;
  }
}
