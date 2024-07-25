<?php

/*
    $desc = array of results
        $item = array of ['s'] => score and ['h'] => row of proson used
*/

// Scores for metathesi music schools algorithm 
function CalculateScoreForMS($uid,$cid,$placeid,$posid,&$desc = array(),$whatpref = 0,$typems = 0)
{
    global $required_check_level,$rejr;

    $max_tpex = 6.0;
    $max_uni = 34.0;
    $max_odeio = 18.0;
    $max_proy = 20.0;
    $max_koin = 18.0;

    // Music Schools Calculator
    $contestrow = Single("CONTESTS","ID",$cid); 
    if (!$contestrow)
        return -1;
    
    $userrow = Single("USERS","ID",$uid); 
    if (!$userrow)
        return -1;

    $placerow = Single("PLACES","ID",$placeid);
    $posrow = Single("POSITIONS","ID",$posid);

    $Has_Diploma_For_Position = 0;
    $Has_Uni_For_Position = 0;

    $score = 0.0;

    $moria_tpe = 0.0;
    $moria_languages = 0.0;

    $moria_conservatoire_instrument = 0.0;
    $moria_ptychio_antfug = 0.0;
    $moria_diplomasodeiou = 0.0;

    $moria_uni = 0.0;
    $moria_proyp1 = 0.0;
    $moria_proyp2 = 0.0;
    $moria_79 = 0.0;
    $moria_y = 0.0;
    $moria_k = 0.0;

    $moria_1 = 0.0;

    // Load all prosonta and their parameters
    $all_prosonta = array();
    $time = time();
    $q0 = QQ("SELECT * FROM PROSON WHERE STATE >= ? AND STARTDATE < ? AND (ENDDATE > ? OR ENDDATE = 0) AND UID = ?",array($required_check_level,$time,$time,$uid));
    while($r0 = $q0->fetchArray())
    {
        $prx = array();
        $prx['params'] = array();
        $x0 = QQ("SELECT * FROM PROSONPAR WHERE PID = ? ORDER BY PIDX",array($r0['ID']));
        while($y0 = $x0->fetchArray())
        {
            $prx['params'][] = $y0;
        }
        $prx['row'] = $r0;
        $all_prosonta[] = $prx;
    }


    // TPE Epimorfosi 701
    // 0.5 A, 1 B, 1.5 B2
    if (1)
    {
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 701) continue;

            $used = array();
            foreach($proson['params'] as $param)
            {
                $pidx = $param['PIDX'];
                if ($pidx != 1)
                    continue;
                   
                if (($param['PVALUE'] == 1) && $moria_tpe < 0.5) 
                {
                    $moria_tpe = 0.5;
                    $used = $r1;
                }
                if (($param['PVALUE'] == 2) && $moria_tpe < 1.0) {
                    $moria_tpe = 1.0;
                    $used = $r1;
                }
                if (($param['PVALUE'] == 3) && $moria_tpe < 1.5) {
                    $moria_tpe = 1.5;
                    $used = $r1;
                }        

                if (($param['PVALUE'] == 4) && $moria_tpe < 2.0) {
                    $moria_tpe = 2.0;
                    $used = $r1;
                }        
            }

            if ($moria_tpe > 0)
            {
                $d1 = array('s' => $moria_tpe,'h' => array($used));
                $desc []= $d1;
            }
        }
    }

    // Foreign Languages
    // 0.5 B2, 1 G2
    // MAX 6 with TPE
    if (1)
    {
        $languages_av = array();
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 2) continue;
            foreach($proson['params'] as $param)
            {
                $pidx = $param['PIDX'];
                if ($pidx == 1)
                {
                    $langx = $param['PVALUE'];
                    if (!array_key_exists($langx,$languages_av))
                        $languages_av[$langx] = array("s" => 0,"u" => array());
                }
                if ($pidx == 2)
                {
                    $lev = $param['PVALUE'];
                    if ($lev >= 2 && $languages_av[$langx]['s'] < 0.5) 
                    {
                        $languages_av[$langx]['s'] = 0.5;
                        $languages_av[$langx]['u'] = $r1;
                    }
                    if ($lev >= 4 && $languages_av[$langx]['s'] < 1.0) 
                    {
                        $languages_av[$langx]['s'] = 1.0;
                        $languages_av[$langx]['u'] = $r1;
                    }
                }
            }
        }
        foreach($languages_av as $langu)
        {
            $moria_languages += $langu['s'];
            $d1 = array('s' => $langu['s'],'h' => array($langu['u']));
            $desc []= $d1;
        }
    }

    // Total TPE+LANG
    if (1)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Συνολικά Μόρια Γλωσσών + ΤΠΕ (Μέγιστο: $max_tpex)</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => min(($moria_tpe + $moria_languages),$max_tpex),'h' => $j);
        $desc []= $d1;
    }

    // Diploma/Ptychio Organou
    if (1)
    {
        $instruments_av = array();
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 401 && $r1['CLASSID'] != 405) continue;

            foreach($proson['params'] as $param)
            {
                $pidx = $param['PIDX'];
                if ($pidx == 3)
                {
                    $langx = $param['PVALUE'];
                    if (!array_key_exists($langx,$instruments_av))
                        $instruments_av[$langx] = array("s" => 0,"u" => array());
    
                    if ($r1['CLASSID'] == 401)     
                        {
                            $instruments_av[$langx]['s'] = 4.0;

                            // Check position
                            if ($posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents($langx)))
                                $Has_Diploma_For_Position = 1;
                        }
                    if ($r1['CLASSID'] == 405 && $instruments_av[$langx]['s'] < 2.0)      
                        $instruments_av[$langx]['s'] = 2.0;
                    $instruments_av[$langx]['u'] = $r1;
                }
            }
        }
        foreach($instruments_av as $langu)
        {
            $moria_conservatoire_instrument += $langu['s'];
            $d1 = array('s' => $langu['s'],'h' => array($langu['u']));
            $desc []= $d1;
        }
    }


    // Ptychio Ant/Fug
    $useantfug = null;
    if (1)
    {
        $use = array();
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 408) continue;

            foreach($proson['params'] as $param)
            {
                $pidx = $param['PIDX'];
                if ($pidx == 3)
                {
                    $lev = $param['PVALUE'];
                    if ($lev >= 3)
                    {
                        $moria_ptychio_antfug = 2;
                        $useantfug = $r1;

                        // Check position
                        if ($posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents("Θεωρητικά Ευρωπαϊκής Μουσικής")))
                            $Has_Diploma_For_Position = 1;
                    }
                }
            }
        }
    }
        
    // Dipl Orch (4)/Byz (4),Synth (3)/Chor (3)
    if (1)
    {
        $unique_types = array();
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 407) continue;

            // 1 chorus,2 synth,3 byz,4 orch,
            foreach($proson['params'] as $param)
            {
                $type = $param['PIDX'];
                if (in_array($type,$unique_types))
                    continue;

                $unique_types[] = $type;
                if ($type == 1 || $type == 2)
                {
                    // Dipl Synthesis, af Ptychio Fuguas
                    if ($type == 2)
                    {
                        $moria_ptychio_antfug = 0;
                        $useantfug = null;
                    }
                    $moria_diplomasodeiou += 3.0;
                    $d1 = array('s' => 3,'h' => array($r1));
                    $desc []= $d1;
                }
                if ($type == 3 || $type == 4)
                {
                    $moria_diplomasodeiou += 4.0;
                    $d1 = array('s' => 4,'h' => array($r1));
                    $desc []= $d1;

                    // Check position
                    if ($type == 3 && $posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents("Θεωρητικά Βυζαντινής Μουσικής")))
                        $Has_Diploma_For_Position = 1;
                }
            }
        }
    }

    if ($moria_ptychio_antfug > 0)
    {
        $d1 = array('s' => 2,'h' => array($useantfug));
        $desc []= $d1;
    }

    
    // Total Conservatoire
    if (1)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Συνολικά Μόρια Ωδείου (Μέγιστο: $max_odeio)</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => min(($moria_conservatoire_instrument + $moria_ptychio_antfug + $moria_diplomasodeiou),$max_odeio),'h' => $j);
        $desc []= $d1;
    }


    // University
    if (1)
    {
        $unique_types = array();

        $ex1 = array();

        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 101 && $r1['CLASSID'] != 102 && $r1['CLASSID'] != 103 && $r1['CLASSID'] != 104) continue;

            
            // Param pid [6,8,7,7] the music eidikeysi
            $whatpid = 0;
            if ($r1['CLASSID'] == 101) $whatpid = 6;
            if ($r1['CLASSID'] == 102) $whatpid = 8;
            if ($r1['CLASSID'] == 103) $whatpid = 7;
            if ($r1['CLASSID'] == 104) $whatpid = 7;





            $cur_idr = '';
            $cur_sx = '';
            $cur_tm = '';
            foreach($proson['params'] as $param)
            {   
                if ($param['PIDX'] == 1) 
                {
                    if (!array_key_exists($param['PVALUE'],$ex1))
                        $ex1[$param['PVALUE']] = array();
                    $cur_idr = $param['PVALUE'];
                    continue;
                }
                if ($param['PIDX'] == 2) 
                {
                    if (!array_key_exists($param['PVALUE'],$ex1[$cur_idr]))
                        $ex1[$cur_idr][$param['PVALUE']] = array();
                    $cur_sx = $param['PVALUE'];
                    continue;
                }
                if ($param['PIDX'] == 3) 
                {
                    if (!array_key_exists($param['PVALUE'],$ex1[$cur_idr][$cur_sx]))
                        $ex1[$cur_idr][$cur_sx][$param['PVALUE']] = array("s" => 0);
                    $cur_tm = $param['PVALUE'];
                    continue;
                }

                if ($param['PIDX'] == $whatpid)
                {
                    if ($param['PVALUE'] == "Μη Πτυχίο ΤΜΣ") // No TMS Pty
                    {
                        $mu = 0;
                        if ($r1['CLASSID'] == 101) $mu = 5.0;
                        if ($r1['CLASSID'] == 102) $mu = 7.0;
                        if ($r1['CLASSID'] == 103) $mu = 11.0;
                        if ($r1['CLASSID'] == 104) $mu = 13.0;

                        if ($r1['CLASSID'] == 102)
                        {
                            foreach($proson['params'] as $param3)
                            {   
                                if ($param3['PIDX'] == 7 && $param3['PVALUE'] == 2)
                                    $mu--;
                            }                
                        }

                        if ($ex1[$cur_idr][$cur_sx][$cur_tm]['s'] < $mu)
                        {
                            $ex1[$cur_idr][$cur_sx][$cur_tm]['s'] = $mu;
                            $ex1[$cur_idr][$cur_sx][$cur_tm]['h'] = array($r1);
                        }
                    }
                    else
                    {
                        $mu = 0;
                        if ($r1['CLASSID'] == 101) $mu = 8.0;
                        if ($r1['CLASSID'] == 102) $mu = 10.0;
                        if ($r1['CLASSID'] == 103) $mu = 14.0;
                        if ($r1['CLASSID'] == 104) $mu = 16.0;

                        if ($r1['CLASSID'] == 102)
                        {
                            foreach($proson['params'] as $param3)
                            {   
                                if ($param3['PIDX'] == 7 && $param3['PVALUE'] == 2)
                                    $mu--;
                            }                
                        }

                        // And check if this is eidikeysi
                        $instr = $param['PVALUE'];
                        if (!$Has_Uni_For_Position && $posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents($instr)))
                            {
                                $Has_Uni_For_Position = 1;
                                $mu += 4.0;
                            }

                        if ($ex1[$cur_idr][$cur_sx][$cur_tm]['s'] < $mu)
                        {
                            $ex1[$cur_idr][$cur_sx][$cur_tm]['s'] = $mu;
                            $ex1[$cur_idr][$cur_sx][$cur_tm]['h'] = array($r1);
                        }
                    }
                }
            }

            foreach($proson['params'] as $param)
            {   
                if ($param['PIDX'] == $whatpid)
                {
                    if ($param['PVALUE'] >= 1)
                    {
                        if ($posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents('Θεωρητικά Ευρωπαϊκής Μουσικής')))
                            $Has_Uni_For_Position = 1;
                    }
                }
            }

        }
        foreach($ex1 as $ex11)
        {
            foreach($ex11 as $ex111)
            {
                foreach($ex111 as $ex1111)
                {
                    if ($ex1111['s'] == 0) continue;
                    $d1 = array('s' => $ex1111['s'],'h' => $ex1111['h']);
                    $desc []= $d1;
                    $moria_uni += $ex1111['s'];
                }
            }
        }
    }



    // Total University
    if (1)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Συνολικά Μόρια Πανεπιστημιακής Εκπαίδευσης (Μέγιστο: $max_uni)</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => min($moria_uni,$max_uni),'h' => $j);
        $desc []= $d1;
    }




    // Koin
    if (1)
    {
        $isGamos = 0;$isMono = 0;
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 501 && $r1['CLASSID'] != 502 && $r1['CLASSID'] != 503)  
                continue;
            foreach($proson['params'] as $param)
            {
                // Entopi/Sinipi
                if (($r1['CLASSID'] == 501 || $r1['CLASSID'] == 503) && $param['PIDX'] == 2 && $placerow)
                {
                    $placename = $placerow['DESCRIPTION'];
                    $iname = $param['PVALUE'];

                    if (mb_strtolower(RemoveAccents($iname)) == mb_strtolower(RemoveAccents($placename)))
                    {
                        $moria_k += 4.0;
                        $d1 = array('s' => 4.0,'h' => array($r1));
                        $desc []= $d1;
                    }
                }

                // Gamos
                if ($r1['CLASSID'] == 502 && $param['PIDX'] == 1 && !$isGamos && !$isMono)
                {
                    if ($param['PVALUE'] == 2)
                    {
                        $moria_k += 2.0;
                        $isGamos = 1;
                        $d1 = array('s' => 2.0,'h' => array($r1));
                        $desc []= $d1;
                    }
                }
                // Monog
                if ($r1['CLASSID'] == 502 && $param['PIDX'] == 4 && !$isGamos && !$isMono)
                {
                    if ($param['PVALUE'] == 2)
                    {
                        $moria_k += 2.0;
                        $isMono = 1;
                        $d1 = array('s' => 2.0,'h' => array($r1));
                        $desc []= $d1;
                    }
                }

                // Kids
                if ($r1['CLASSID'] == 502 && $param['PIDX'] == 2)
                {
                    $kidm = 0;
                    if ($param['PVALUE'] == 1) $kidm = 2;
                    if ($param['PVALUE'] == 2) $kidm = 4;
                    if ($param['PVALUE'] >= 3) $kidm = 8;
                    //if ($param['PVALUE'] >= 4) $kidm = 10;

                    if ($kidm)
                    {
                        $moria_k += $kidm;
                        $d1 = array('s' => $kidm,'h' => array($r1));
                        $desc []= $d1;
                    }
                }
            }
       }
    }

    // Total Koin
    if (1)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Συνολικά Μόρια Κοινωνικής Κατάστασης (Μέγιστο: $max_koin)</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => min($moria_k,$max_koin),'h' => $j);
        $desc []= $d1;
    }

    // Ypir
    $years_mousika = 0;
    $years_total = 0;
    if (1)
    {
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 601) 
                continue;
            $value_mousika = 0;
            foreach($proson['params'] as $param)
            {
                if ($param['PIDX'] == 1)
                {
                    if ($param['PVALUE'] == "ΠΕ79" || $param['PVALUE'] == "ΠΕ79.01")
                    {
//                        $d1 = array('s' => 2,'h' => array($r1));
  //                      $d1['h'][0]['DESCRIPTION'] = "ΠΕ79";
    //                    $desc []= $d1;
                        $moria_79 = 2.0;
                    }
                }
                if ($param['PIDX'] == 4)
                {
                    // Proyp Mousika
                    $v = $param['PVALUE'];
                    $value_mousika = $v;
                    $years = (int)($v / 360);
                    $years_mousika = $years;
                    $v %= 360;
                    $months = (int)($v / 30);
                    $v %= 30;
                    $days = $v;
                

                    $resv = round($years * 2.0 + $months / 6.0 + $days/180.0,2);
                    $moria_proyp2 = $resv;
                    $d1 = array('s' => $resv,'h' => array($r1));
                    $d1['h'][0]['DESCRIPTION'] = "Προυπηρεσία Μουσικά";
                    $desc []= $d1;
                }
            }
            foreach($proson['params'] as $param)
            {
                if ($param['PIDX'] == 9)
                {
                    // Proyp Genika
                    $v = $param['PVALUE'];
                    $years_total = (int)($v / 360);
                    $v -= $value_mousika;
                    $years = (int)($v / 360);
                    $v %= 360;
                    $months = (int)($v / 30);
                    $v %= 30;
                    $days = $v;
                
                    $resv = round($years * 0.5 + $months / 24.0 + $days/720.0,2);
                    $moria_proyp1 = $resv;
                    $d1 = array('s' => $resv,'h' => array($r1));
                    $d1['h'][0]['DESCRIPTION'] = "Προυπηρεσία Γενικά";
                    $desc []= $d1;
                }
            }


        }

        $moria_y = min($moria_proyp1 + $moria_proyp2,$max_proy);
    }

    
    // Total Proyp
    if (1)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Συνολικά Μόρια προϋπηρεσίας (Μέγιστο: $max_proy)</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => min($moria_y,$max_proy),'h' => $j);
        $desc []= $d1;
    }


    // Prwti
    if ($whatpref == 1)
    {
        $moria_1 = 2.0;
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Μόρια πρώτης προτίμησης</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => $moria_1,'h' => $j);
        $desc []= $d1;
    }
     
    if ($moria_79 > 0)
    {
        $j = array();
        $j[0] = array("DESCRIPTION" => "<b>Μόρια ΠΕ79</b>","CLASSID" => 0,"ID" => 0);
        $d1 = array('s' => $moria_79,'h' => $j);
        $desc []= $d1;
    }

    
    // Forced
    if ($typems == 0)
    {
        // Met
        if ($years_total < 5)
        {
            $desc = array();
            $rejr = "Απαιτείται ελάχιστη προϋπηρεσία στη γενική εκπαίδευση, τουλάχιστον 5 έτη.";
            return -1;
        }
        if ($years_mousika < 5)
        {
            $desc = array();
            $rejr = "Απαιτείται ελάχιστη προϋπηρεσία στα μουσικά σχολεία, τουλάχιστον 5 έτη.";
            return -1;
        }
    }
    if ($typems == 1)
    {
        // Apos
        if ($years_total < 5)
        {
            $desc = array();
            $rejr = "Απαιτείται ελάχιστη προϋπηρεσία στη γενική εκπαίδευση, τουλάχιστον 5 έτη.";
            return -1;
        }
    }
    
    if (!$Has_Diploma_For_Position && !$Has_Uni_For_Position)
    {
        if ($posrow)
        {
            $desc = array();
            $rejr = "Λείπει προαπαιτούμενο: Δίπλωμα οργάνου ή ΤΜΣ με ειδίκευση: ". $posrow['DESCRIPTION'];
            return -1;
        }
    }



    /*
        Ptychio - Met - Did - PostPhD 
        5,7,11,13       Other
        8,10,14,16       TMS
        +4               TMS + Eid for the place
        MAX 36

        Odeio
        Dipl  Org/Orch/Byz/ 4
        Dipl  Orch/Chor/    3
        Pty   Ant/Fug       2
        MAX 18

        Apait Either PT + Eid or Dipl Org

        Proy 
        2*mous + 0.5*gen max 20
        PE79 + 2
        Entop +4
        PP +2
        Sinip +4
        Gamos/Monog +2
        Paidia 2,4,8,10
        MAX 40



    */

    
    $score = min(($moria_tpe + $moria_languages),$max_tpex) + min(($moria_conservatoire_instrument + $moria_ptychio_antfug + $moria_diplomasodeiou),$max_odeio) + min($moria_uni,$max_uni) + min($moria_y,$max_proy)+ min($moria_k,$max_koin) + $moria_1 + $moria_79;
    return min($score,100.0);
}


function CalculateScore($uid,$cid,$placeid,$posid,$debug = 0,&$linkssave = array(),$prosononly = 0,&$desc = array(),$forwhichplace = 0,$forwhichpos = 0,$whatpref = 0)
{
    global $rejr,$xmlp,$required_check_level,$first_pref_score;
    EnsureProsonLoaded();
    $pr = Single("USERS","ID",$uid);
    if (!$pr)
        return -1;
    $contestrow = Single("CONTESTS","ID",$cid); 
    if (!$contestrow)
        return -1;
    if ($contestrow['CLASSID'] == 101)
    {
        return CalculateScoreForMS($uid,$cid,$placeid,$posid,$desc,$whatpref,0);
    }
    if ($contestrow['CLASSID'] == 102)
    {
        return CalculateScoreForMS($uid,$cid,$placeid,$posid,$desc,$whatpref,1);
    }
    $posr = Single("POSITIONS","ID",$posid); 
    $score = 0;



    // If we have a posid, search for generic by thesi name
    // else we search for generic
    $thesiname = '';
    if ($posr)
        $thesiname = $posr['DESCRIPTION'];
    $CountGeneral = QQ("SELECT COUNT(*) FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND FORTHESI = ?",array($cid,$thesiname))->fetchArray()[0];
    if ($CountGeneral && $thesiname != '')
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND FORTHESI = ?",array($cid,$thesiname));
    else
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
    while($r1 = $q1->fetchArray())
    {
        $desc2 = array();
        $sp = $r1['SCORE'];
        $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
        if ($rootc)
            $params_root = $rootc->params;
        if ($params_root)
            {
                foreach($params_root->p as $param)
                {
                    $pa = $param->attributes();                              
                    $partypes[(int)$pa['id']] = $pa['t'];                       
                }    
            }
        $wouldeval = 0;
        if (strstr($sp,'$values'))
        {
            $wouldeval = 1;
        }

        $min_needed = 1;
        if ((int)$r1['MINX'] > 0)
            $min_needed = (int)$r1['MINX'];
        $max_needed = 0;
        if ((int)$r1['MAXX'] > 0)
            $max_needed = (int)$r1['MAXX'];

        for($deep = 0 ; ; $deep++)
        {
            if ($deep > 0 && $max_needed > 0 && $deep >= $max_needed)
                break;
            $checked = array();
            $reason = '';

            $haslist = array();
            $has = ProsonResolutAndOrNot($uid,$r1['ID'],$checked,$deep,$reason,$haslist);

            if ($has != 1)
            {
                if ($sp > 0 || $wouldeval == 1 || $deep >= $min_needed)
                    break; // not required
                $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
                $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s <br><span style="text-color:gray">%s x%s</span>',$rootc->attributes()['t'],$reason,$min_needed);
                return -1;    
            }   

            // He has it, 
            $forced_proson_id = -1;
            if ($wouldeval || $prosononly)
            {
                $sp = $r1['SCORE'];
                $deeps = $deep;
                $time = time();
                $qpr = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE >= ? AND STARTDATE < ? AND (ENDDATE > ? OR ENDDATE = 0)",array($uid,$r1['PROSONTYPE'],$required_check_level,$time,$time));
        
                while($rpr = $qpr->fetchArray())
                {
                   if ($deeps > 0)
                    {
                        $deeps--;
                        continue;
                    }
                    $forced_proson_id = $rpr['ID'];
                    $pars = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($rpr['ID']));
                    while($par = $pars->fetchArray())
                    {
                        $p_idx = $par['PIDX'];
                        $p_val = $par['PVALUE'];

                        // Check if it's date
                        if ($partypes[$p_idx] == 3)
                        {
                            $startwhen = $rpr['STARTDATE'];
                            $now = time();
                            if ($now > $startwhen)
                            {
                                $a1 = From360ToActual($p_val);
                                $a1 += ($now - $startwhen);
                                $p_val = FromActualTo360($a1);
                            }
                        }
                        if ($p_val == '') $p_val = 0;
                        $sp = str_replace(sprintf('$values[%s]',$p_idx),$p_val,$sp);
                    }
                }
                if (strstr($sp,'$values'))
                    $sp = 0;
                else
                    $sp = eval2($sp,$uid,$cid,$placeid,$posid);
            }

        if ($debug)
            {
                if ($sp > 0)
                    printf("%s: %s<br>",$rootc->attributes()['t'],$sp);
            }


        //  Forced
//        if ($r1['PROSONTYPE'] == 2) xdebug_break();
        $forcedscore = QQ("SELECT * FROM PROSONFORCE WHERE (UID = ? OR UID = 0) AND (CID = ? OR CID = 0) AND (PLACEID = ? OR PLACEID = ? OR PLACEID = 0) AND (POS = ? OR POS = ? OR POS = 0) AND (PIDCLASS = ? OR PIDCLASS = 0) AND (PRID = ? OR PRID = 0)",array($uid,$cid,$placeid,$forwhichplace,$posid,$forwhichpos,$r1['PROSONTYPE'],$forced_proson_id))->fetchArray();
        if ($forcedscore)
            {
                $sp = $forcedscore['SCORE'];
            }


        $desc2['s'] = $sp;
        $desc2['h'] = $haslist;
        
        $desc[] = $desc2;
        $score += $sp;
        }

        
    }


    if ($posid)
    {
        if ($whatpref == 1)
            $score += $first_pref_score;
        $v = CalculateScore($uid,$cid,$placeid,0,$debug,$linkssave,$prosononly,$desc,0,$posid);
        if ($v == -1)
            return -1;
        $score += $v;
    }
    else
    if ($placeid)
    {
        $v =  CalculateScore($uid,$cid,0,0,$debug,$linkssave,$prosononly,$desc,$placeid,$forwhichpos);
        if ($v == -1)
            return -1;
        $score += $v;
    }

    return $score;

}


function PrintDescriptionFromScore($desc,$onlypos = false)
{
    $s = '<table class="datatable" style="width: 100%">';
    $s .= '<thead><th>#</th><th>Προσόν</th><th>Μόρια</th></thead><tbody>';
    
    $c = 1;
    foreach($desc as $d)
    {
        if((float)$d['s'] == 0 && $onlypos)    
            continue;
        $s .= sprintf('<tr>');
        $info = '';
        $is_sum = 0;
        foreach($d['h'] as $dd)
        {
            $info .= sprintf("%s<br>",$dd['DESCRIPTION']);
            if ($dd['ID'] == 0)
                $is_sum = 1;
        }
        if ($is_sum)
            $s .= sprintf('<td></td>');
        else
            $s .= sprintf('<td>%s</td>',$c);
        $s .= sprintf('<td>%s</td>',$info);
        if ($is_sum)
            $s .= sprintf('<td><b>%s</b></td>',$d['s']);
        else
            $s .= sprintf('<td>%s</td>',$d['s']);
        $s .= sprintf('</tr>');
        if (!$is_sum)
            $c++;
    }

    $s .= '</tbody>';
    $s .= '</table>';
    return $s;
}
