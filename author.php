<?php


function inscript_author_get ($id)
{
  if (isset ($args['id']))
    return get_userdata ($args['id']);
  else if (isset ($args['login']))
    return get_userdatabylogin ($args['login']);
  else
  {
    global $post;
    if ($post->ID)
      return get_userdata ($post->post_author);
    return get_userdata (0);
  }
}



function inscript_func_author ($args)
{
  return the_author (isset ($args['idmode']) ? $args['idmode'] : '', false);
}

function inscript_func_author_login ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_login;
}

function inscript_func_author_firstname ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_firstname;
}

function inscript_func_author_lastname ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_lastname;
}

function inscript_func_author_nickname ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_nickname;
}

function inscript_func_author_fullname ($args)
{
  $author = inscript_author_get ($args);
	$idmode = $author->user_idmode;
	if ($idmode == 'login')     return $author->user_login;
	if ($idmode == 'firstname') return $author->user_firstname;
	if ($idmode == 'lastname')  return $author->user_lastname;
	if ($idmode == 'namefl')    return $author->user_firstname.' '.$author->user_lastname;
	if ($idmode == 'namelf')    return $author->user_lastname.' '.$aurhot->user_firstname;
	return $userdata->user_nickname;
}

function inscript_func_author_description ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_description;
}

function inscript_func_author_id ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_description;
}

function inscript_func_author_email ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_email;
}

function inscript_func_author_url ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_url;
}

function inscript_func_author_icq ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_icq;
}

function inscript_func_author_aim ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_aim;
}

function inscript_func_author_yim ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_yim;
}

function inscript_func_author_msn ($args)
{
  $author = inscript_author_get ($args);
  return $author->user_msn;
}

function inscript_func_author_numposts ($args)
{
  $author = inscript_author_get ($args);
  return get_usernumposts ($author->ID);
}


?>