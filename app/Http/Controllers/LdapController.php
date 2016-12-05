<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Adldap\Adldap;
use Adldap\Connections\Provider;

class LdapController extends Controller
{

    protected $servers = [

        'utwente' => [
            'account_prefix' => '',
            'account_suffix' => '@utwente.nl',
            'domain_controllers' => ['ldapauth-dc.ad.utwente.nl'],
            'port' => 636,
            'timeout' => 5,
            'base_dn' => 'OU=Accounts,DC=ad,DC=utwente,DC=nl',
            'follow_referrals' => false,
            'use_ssl' => true,
            'use_tls' => false,
        ],

        'proto' => [
            'account_prefix' => '',
            'account_suffix' => '@net.saproto.nl',
            'domain_controllers' => ['ad.proto.utwente.nl'],
            'port' => 636,
            'timeout' => 5,
            'base_dn' => 'OU=Proto,DC=net,DC=saproto,DC=nl',
            'follow_referrals' => false,
            'use_ssl' => true,
            'use_tls' => false,
        ]

    ];

    public function search(Request $request, $server)
    {

        switch ($server) {
            case 'proto':
                if (!FirewallController::isWhitelisted($request->ip())) {
                    abort(403);
                }
                break;
            case 'utwente':
                if (!FirewallController::isOnCampus($request->ip())) {
                    abort(403);
                }
                break;
        }

        if (!array_key_exists($server, $this->servers)) {
            abort(404);
        }

        if (!$request->has('filter')) {
            abort(500);
        }

        $config = $this->servers[$server];
        $config['admin_username'] = env('LDAP_' . strtoupper($server) . '_USER');
        $config['admin_password'] = env('LDAP_' . strtoupper($server) . '_PASS');

        $ad = new Adldap();
        $provider = new Provider($config);
        $ad->addProvider('ldap', $provider);
        $ad->connect('ldap');

        $filter = [
            '(objectClass=organizationalPerson)',
            '(' . $request->filter . ')'
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
            'postalCode',
            'l',
            'preferredLanguage',
            'streetAddress',
            'sAMAccountName',
            'wWWHomePage'
        ];

        return $provider->search()->select($select)->rawFilter($filter)->get();

    }

}