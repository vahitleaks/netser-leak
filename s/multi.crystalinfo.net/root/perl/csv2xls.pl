#!/usr/bin/perl -w

    use strict;
    use Spreadsheet::WriteExcel;

    open (TABFILE, "/usr/local/wwwroot/www.crystalinfo.net/root/perl/outbound.csv") or die "outbound.csv: $!";

    my $workbook  = Spreadsheet::WriteExcel->new("/home/seykay/test/tab.xls");
    my $worksheet = $workbook->add_worksheet();

 # Add a Format
   my $format = $workbook->addformat();
   $format->set_bold();
   $format->set_size(15);
   $format->set_color('blue');
   $format->set_align('center');
					 

    # Row and column are zero indexed
    my $row = 0;

    while (<TABFILE>) {
        chomp;
        # Split on single tab
        my @Fld = split(';', $_);

        my $col = 0;
        foreach my $token (@Fld) {
	    if($row==0){
               $worksheet->write($row, $col, $token, $format);
	    }else{
               $worksheet->write($row, $col, $token);
	    }
            $col++;
        }
        $row++;
    }
