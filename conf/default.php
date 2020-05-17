<?php
/**
 * Config vars for the plugin
 */

// array of OIDC issuer domains (required)
$conf['issuers']   = '';

// cache authentication key (requred; should be set to a random constant)
$conf['cachekey']   = '';

// group required in the JWT token to grant any access (optional, requirement disabled if empty)
$conf['required_group']   = '';

// mapping groups from JWT token groups to DokuWiki groups
// At least some groups should map into "user" to receive generic "logged-in user" ACL permissions 
$conf['map_groups'] = ["team" => "user"];