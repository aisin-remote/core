<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $employees = [
            [
                'npk' => '0158',
                'name' => 'I NENGAH SURYADI',
                'phone_number' => '081318405806',
                'birthday_date' => '1971-01-22',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1990-07-04',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0161',
                'name' => 'OPIK TAOPIK PIKRAP',
                'phone_number' => '081514674886',
                'birthday_date' => '1972-12-07',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1990-07-04',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0155',
                'name' => 'SUTARJA',
                'phone_number' => '081310177060',
                'birthday_date' => '1971-03-17',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1990-08-22',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0209',
                'name' => 'TEGOEH SETYADI',
                'phone_number' => '08811652497',
                'birthday_date' => '1970-09-01',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-06-24',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0208',
                'name' => 'RUDY HENDRAWAN',
                'phone_number' => '085891165366',
                'birthday_date' => '1971-03-15',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-07-08',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0223',
                'name' => 'SUNARDI',
                'phone_number' => '081288777923',
                'birthday_date' => '1970-10-26',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-07-29',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0225',
                'name' => 'DENI AANG SUHENDAR',
                'phone_number' => '083815675516',
                'birthday_date' => '1973-08-13',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-07-29',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0222',
                'name' => 'NGATIJO',
                'phone_number' => '081584411816',
                'birthday_date' => '1970-07-19',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-07-29',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0217',
                'name' => 'HARDIANTO',
                'phone_number' => '081808284128',
                'birthday_date' => '1971-11-23',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-07-29',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0233',
                'name' => 'AGUS SARIF',
                'phone_number' => '081398440473',
                'birthday_date' => '1973-07-10',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-08-12',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0237',
                'name' => 'SUKIRMAN',
                'phone_number' => '085218396844',
                'birthday_date' => '1973-05-09',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1991-10-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0342',
                'name' => 'DENI SEPTIAWAN',
                'phone_number' => '08128014272',
                'birthday_date' => '1972-09-07',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1992-05-18',
                'position' => 'Section Head'
            ],
            [
                'npk' => '0256',
                'name' => 'ROSMIATY',
                'phone_number' => '081295946181',
                'birthday_date' => '1972-09-04',
                'gender' => 'Female',
                'company_name' => 'AII',
                'aisin_entry_date' => '1992-09-15',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0311',
                'name' => 'SUHARYANTO',
                'phone_number' => '08128425944',
                'birthday_date' => '1974-11-18',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-02-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0330',
                'name' => 'LS. SUJARWO',
                'phone_number' => '081314009075',
                'birthday_date' => '1972-03-25',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-02-15',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0339',
                'name' => 'MUSKHOLIS',
                'phone_number' => '088898274333',
                'birthday_date' => '1974-09-02',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-05-04',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0360',
                'name' => 'SUDILAN',
                'phone_number' => '08561025672',
                'birthday_date' => '1971-06-15',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-05-25',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0372',
                'name' => 'EVA SUDARYANTO',
                'phone_number' => '085694678492',
                'birthday_date' => '1971-06-18',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-06-06',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0358',
                'name' => 'WIDARTO',
                'phone_number' => '081317994696',
                'birthday_date' => '1974-04-22',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-10-05',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0353',
                'name' => 'ABDUL AZIS',
                'phone_number' => '087840209349',
                'birthday_date' => '1975-11-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-11-16',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0354',
                'name' => 'SULATNO',
                'phone_number' => '081281713836',
                'birthday_date' => '1975-11-17',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1994-11-16',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0415',
                'name' => 'ARI SETIAWAN',
                'phone_number' => '085711942561',
                'birthday_date' => '1975-01-05',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1995-03-15',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0383',
                'name' => 'DARYONO',
                'phone_number' => '081286724083',
                'birthday_date' => '1972-10-06',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1995-03-15',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0469',
                'name' => 'BOWO ISKANDAR',
                'phone_number' => '088213276693',
                'birthday_date' => '1978-01-24',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1995-07-03',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0468',
                'name' => 'ABETNICO',
                'phone_number' => '081280675975',
                'birthday_date' => '1976-01-12',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1995-07-03',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0379',
                'name' => 'HERI SUPRIJATNA',
                'phone_number' => '08161618184',
                'birthday_date' => '1970-11-22',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1995-07-26',
                'position' => 'Manager'
            ],
            [
                'npk' => '0472',
                'name' => 'TOPO SUHARSO',
                'phone_number' => '08129997610',
                'birthday_date' => '1974-12-13',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-01-04',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0427',
                'name' => 'JANTO RAHARJO',
                'phone_number' => '08158706224',
                'birthday_date' => '1972-01-06',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-04-01',
                'position' => 'Manager'
            ],
            [
                'npk' => '0485',
                'name' => 'NURDIANA RASMAWAN',
                'phone_number' => '087877718306',
                'birthday_date' => '1976-10-09',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-04-16',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0517',
                'name' => 'ADE AGUS HIDAYAT',
                'phone_number' => '087770032840',
                'birthday_date' => '1972-08-24',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-04-16',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0538',
                'name' => 'ENDANG SWARA',
                'phone_number' => '08567070605',
                'birthday_date' => '1975-05-10',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-05-14',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0533',
                'name' => 'TRIYONO',
                'phone_number' => '081281027750',
                'birthday_date' => '1975-03-01',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-05-14',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0442',
                'name' => 'HERU SUBROTO',
                'phone_number' => '08159290473',
                'birthday_date' => '1973-05-26',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-06-03',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0529',
                'name' => 'KODIRIN',
                'phone_number' => '081319132177',
                'birthday_date' => '1977-01-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-08-21',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0486',
                'name' => 'MULYANTO',
                'phone_number' => '081298609306',
                'birthday_date' => '1972-11-27',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1996-10-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0512',
                'name' => 'GANI HERYADI N.',
                'phone_number' => '08161343069',
                'birthday_date' => '1971-11-20',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1997-01-02',
                'position' => 'Manager'
            ],
            [
                'npk' => '0483',
                'name' => 'GANGSAR BUDIONO',
                'phone_number' => '081514545967',
                'birthday_date' => '1972-03-16',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1997-01-02',
                'position' => 'Manager'
            ],
            [
                'npk' => '0505',
                'name' => 'TOMI JAYA',
                'phone_number' => '08111147615',
                'birthday_date' => '1975-05-30',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '1997-01-02',
                'position' => 'Act GM'
            ],
            [
                'npk' => '0683',
                'name' => 'IMAM MUCHODI',
                'phone_number' => '085778475388',
                'birthday_date' => '1980-05-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2000-11-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0789',
                'name' => 'AFIF AHMADI',
                'phone_number' => '08128121502',
                'birthday_date' => '1977-07-14',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2001-09-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '0799',
                'name' => 'ARIANTO NUGROHO',
                'phone_number' => '081218541363',
                'birthday_date' => '1979-12-16',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2001-10-01',
                'position' => 'Act GM'
            ],
            [
                'npk' => '0791',
                'name' => 'EDWIN SUSILO',
                'phone_number' => '081280791985',
                'birthday_date' => '1978-12-22',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2001-10-01',
                'position' => 'Act GM'
            ],
            [
                'npk' => '10469',
                'name' => 'ARIA SAPUTRA',
                'phone_number' => '081387750202',
                'birthday_date' => '1975-11-14',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2001-12-01',
                'position' => 'Coordinator'
            ],
            [
                'npk' => '0959',
                'name' => 'IRVAN SENJAYA',
                'phone_number' => '085890025431',
                'birthday_date' => '1980-07-02',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2002-11-18',
                'position' => 'GM'
            ],
            [
                'npk' => '10459',
                'name' => 'IRAWAN HENDRO RAHARJO',
                'phone_number' => '08129511674',
                'birthday_date' => '1971-05-08',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2003-04-01',
                'position' => 'Manager'
            ],
            [
                'npk' => '1002',
                'name' => 'HARI NUGROHO',
                'phone_number' => '081510858889',
                'birthday_date' => '1979-04-01',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2003-05-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '1041',
                'name' => 'JOHANSEN',
                'phone_number' => '08121921648',
                'birthday_date' => '1977-06-19',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2003-07-16',
                'position' => 'Section Head'
            ],
            [
                'npk' => '1061',
                'name' => 'RUDI RUDIYAN',
                'phone_number' => '081219815759',
                'birthday_date' => '1975-05-12',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2003-08-19',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '1066',
                'name' => 'BUDHI JAYA',
                'phone_number' => '087722437111',
                'birthday_date' => '1977-04-12',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2003-08-29',
                'position' => 'GM'
            ],
            [
                'npk' => '10468',
                'name' => 'KOMANG YOGA PRAMANA PUTRA',
                'phone_number' => '081282308384',
                'birthday_date' => '1981-07-04',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-06-01',
                'position' => 'GM'
            ],
            [
                'npk' => '1392',
                'name' => 'HERMANSYAH',
                'phone_number' => '081808282872',
                'birthday_date' => '1978-07-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-06-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '1541',
                'name' => 'BAKHTIAR PURNAMA INSAN KAMIL',
                'phone_number' => '081283276399',
                'birthday_date' => '1980-11-23',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-08-11',
                'position' => 'Section Head'
            ],
            [
                'npk' => '1583',
                'name' => 'NYONO',
                'phone_number' => '085776895046',
                'birthday_date' => '1981-06-11',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-09-08',
                'position' => 'Coordinator'
            ],
            [
                'npk' => '1582',
                'name' => 'RONI SYAHRONI',
                'phone_number' => '081286062396',
                'birthday_date' => '1982-11-02',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-09-08',
                'position' => 'Section Head'
            ],
            [
                'npk' => '1651',
                'name' => 'FAOZAN',
                'phone_number' => '081326409197',
                'birthday_date' => '1982-10-26',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-11-01',
                'position' => 'Section Head'
            ],
            [
                'npk' => '1648',
                'name' => 'PARWOTO',
                'phone_number' => '081314591517',
                'birthday_date' => '1981-07-10',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2004-11-01',
                'position' => 'GM'
            ],
            [
                'npk' => '1733',
                'name' => 'ADITYA AVIANTO NUGROHO',
                'phone_number' => '085697299058',
                'birthday_date' => '1978-04-03',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-02-14',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '1905',
                'name' => 'ARIEF WIDODO',
                'phone_number' => '0818118406',
                'birthday_date' => '1981-01-05',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-07-01',
                'position' => 'GM'
            ],
            [
                'npk' => '1906',
                'name' => 'DODI DARSANA',
                'phone_number' => '085795585680',
                'birthday_date' => '1980-11-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-07-01',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '2099',
                'name' => 'BENEDIKTUS SETIO A.N.',
                'phone_number' => '081353040145',
                'birthday_date' => '1983-11-06',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-09-12',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '2097',
                'name' => 'PIPIN HARYANTO',
                'phone_number' => '081578046315',
                'birthday_date' => '1982-08-13',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-09-12',
                'position' => 'Section Head'
            ],
            [
                'npk' => '2155',
                'name' => 'YANNIEK INGDRIYA SURATMAN',
                'phone_number' => '081573745235',
                'birthday_date' => '1984-05-26',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-10-10',
                'position' => 'Section Head'
            ],
            [
                'npk' => '2191',
                'name' => 'RINI THEODORA',
                'phone_number' => '0811115049',
                'birthday_date' => '1975-09-11',
                'gender' => 'Female',
                'company_name' => 'AII',
                'aisin_entry_date' => '2005-11-09',
                'position' => 'Section Head'
            ],
            [
                'npk' => '2474',
                'name' => 'RUDY SETYAWAN',
                'phone_number' => '081382690479',
                'birthday_date' => '1978-12-02',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2006-07-01',
                'position' => 'Section Head'
            ],
            [
                'npk' => '2714',
                'name' => 'MIFTAKUL FITRA',
                'phone_number' => '081219268247',
                'birthday_date' => '1984-06-21',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2006-11-27',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '3333',
                'name' => 'YOHANNES ADE KRISTIAWAN',
                'phone_number' => '0812810960350',
                'birthday_date' => '1986-03-17',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2008-04-11',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '3394',
                'name' => 'ROSSI WIDJANANTO, ST',
                'phone_number' => '0818255355',
                'birthday_date' => '1982-02-14',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2008-05-23',
                'position' => 'Manager'
            ],
            [
                'npk' => '3527',
                'name' => 'RIDO UTOMO',
                'phone_number' => '083873985832',
                'birthday_date' => '1985-10-24',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2008-10-10',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '3587',
                'name' => 'NADIA AWARDDINI',
                'phone_number' => '08122232484',
                'birthday_date' => '1984-06-26',
                'gender' => 'Female',
                'company_name' => 'AII',
                'aisin_entry_date' => '2009-01-09',
                'position' => 'Coordinator'
            ],
            [
                'npk' => '3901',
                'name' => 'ARIF RACHMANTO',
                'phone_number' => '085229799244',
                'birthday_date' => '1987-10-15',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2009-11-13',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '4151',
                'name' => 'FEBRIDANI MISWAN',
                'phone_number' => '081281090991',
                'birthday_date' => '1987-02-19',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2010-04-23',
                'position' => 'Section Head'
            ],
            [
                'npk' => '4239',
                'name' => 'HERIZAL ARFIANSYAH',
                'phone_number' => '085959169470',
                'birthday_date' => '1986-06-16',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2010-06-04',
                'position' => 'Manager'
            ],
            [
                'npk' => '4525',
                'name' => 'FAISAL RACHMAN PUTRA',
                'phone_number' => '085959559000',
                'birthday_date' => '1987-09-05',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2010-11-12',
                'position' => 'Section Head'
            ],
            [
                'npk' => '4656',
                'name' => 'RATNA WULANDARI',
                'phone_number' => '085793252912',
                'birthday_date' => '1988-09-15',
                'gender' => 'Female',
                'company_name' => 'AII',
                'aisin_entry_date' => '2011-01-03',
                'position' => 'Supervisor'
            ],
            [
                'npk' => '4875',
                'name' => 'TRI WAHYU UTOMO',
                'phone_number' => '081805077005',
                'birthday_date' => '1987-07-14',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2011-04-29',
                'position' => 'Coordinator'
            ],
            [
                'npk' => '4901',
                'name' => 'LIEM NELI ANGGRAENI',
                'phone_number' => '081311288587',
                'birthday_date' => '1989-01-09',
                'gender' => 'Female',
                'company_name' => 'AII',
                'aisin_entry_date' => '2011-06-06',
                'position' => 'Coordinator'
            ],
            [
                'npk' => '4989',
                'name' => 'FAJAR NURAHMAN',
                'phone_number' => '085643604302',
                'birthday_date' => '1988-11-14',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2011-08-01',
                'position' => 'Manager'
            ],
            // New employee
            [
                'npk' => '11818',
                'name' => 'ANANTYA FIKRI RIVALDI',
                'phone_number' => '089649544633',
                'birthday_date' => '2002-09-01',
                'gender' => 'Male',
                'company_name' => 'AII',
                'aisin_entry_date' => '2024-11-05',
                'position' => 'Manager'
            ]
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
