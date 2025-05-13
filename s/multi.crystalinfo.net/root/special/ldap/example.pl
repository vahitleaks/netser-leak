#!/usr/bin/perl
use Net::LDAP;
$ldap = Net::LDAP->new("10.200.20.200");
$ldap->bind("uid=vodaconnector,ou=connectors,ou=people,c=tr,dc=com,o=disbank,o=gds", password=>"voda1234");
$mesg = $ldap->search(filter=>"(objectClass=*)", base=>"dc=vodasoft,dc=org");
@entries = $mesg->entries;
foreach $entry (@entries) {
        print "dn: " . $entry->dn() . "\n";
        @attrs = $entry->attributes();
        foreach $attr (@attrs) {
                printf("\t%s: %s\n", $attr, $entry->get_value($attr));
        }
}
