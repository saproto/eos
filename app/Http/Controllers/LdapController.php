<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Adldap\Adldap;
use Adldap\Connections\Provider;

class LdapController extends Controller
{

    public function search(Request $request)
    {


        if (!FirewallController::isWhitelisted($request->ip())) {
            abort(403);
        }

        if (!$request->has('filter')) {
            abort(500);
        }

        $config = [
            'account_prefix' => '',
            'account_suffix' => '@utwente.nl',
            'domain_controllers' => [getenv('LDAP_SERVER')],
            'port' => 636,
            'timeout' => 5,
            'base_dn' => getenv('LDAP_BASEDN'),
            'follow_referrals' => false,
            'use_ssl' => true,
            'use_tls' => false,
            'admin_username' => env('LDAP_USER'),
            'admin_password' => env('LDAP_PASS')
        ];

        $ad = new Adldap();
        $provider = new Provider($config);
        $ad->addProvider('ldap', $provider);
        $ad->connect('ldap');

        $filter = [
            '(objectClass=organizationalPerson)',
            '(' . urldecode($request->filter) . ')'
        ];

        $select = [
            'givenName',
            'sn',
            'initials',
            'displayName',
            'cn',
            'userPrincipalName',
            'uid',
            'description',
            'mail',
            'department',
            'telephoneNumber',
            'physicaldeliveryofficename',
            'postalCode',
            'l',
            'preferredLanguage',
            'streetAddress',
            'sAMAccountName',
            'wWWHomePage',
            'extensionAttribute6'
        ];

        return $provider->search()->select($select)->rawFilter($filter)->get();

    }

}
