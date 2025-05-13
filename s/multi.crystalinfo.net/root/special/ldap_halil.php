<?php 
    $ldapHost = "10.200.20.162";
    $ldapPort = "3141";
    $ldapUser ="uid=vodaconnector,ou=connectors,ou=people,c=tr,dc=com,o=disbank,o=gds";
    $ldapPswd ="voda1234";
		    
    $ldapLink =ldap_connect($ldapHost, $ldapPort)
        or die("Can't establish LDAP connection");
			
	if (ldap_set_option($ldapLink,LDAP_OPT_PROTOCOL_VERSION,3))
	{
	    echo "Using LDAP v3";
	    }else{
        echo "Failed to set version to protocol 3";
	}
			
	ldap_bind($ldapLink,$ldapUser,$ldapPswd)
    or die("Can't bind to server.");
				    
?>
								         }