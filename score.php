<?php

/*
    $desc = array of results
        $item = array of ['s'] => score and ['h'] => row of proson used
*/

// Scores for metathesi music schools algorithm 
function CalculateScoreForMS($uid,$cid,$placeid,$posid,&$desc = array(),$typems = 0)
{
    global $required_check_level,$rejr;

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

    $score = 0;

    $moria_tpe = 0;
    $moria_languages = 0;

    $moria_conservatoire_instrument = 0;
    $moria_ptychio_antfug = 0;
    $moria_diplomasodeiou = 0;

    $moria_uni = 0;
    $moria_k = 0;

    // Load all prosonta and their parameters
    $all_prosonta = array();
    $q0 = QQ("SELECT * FROM PROSON WHERE STATE >= ? AND UID = ?",array($required_check_level,$uid));
    while($r0 = $q0->fetchArray())
    {
        $prx = array();
        $prx['params'] = array();
        $x0 = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($r0['ID']));
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
                if (($param['PVALUE'] >= 3) && $moria_tpe < 1.5) {
                    $moria_tpe = 1.5;
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
                        $use = $r1;

                        // Check position
                        if ($posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents("Θεωρητικά Ευρωπαϊκής Μουσικής")))
                            $Has_Diploma_For_Position = 1;
                    }
                }
            }
        }
        if ($moria_ptychio_antfug > 0)
        {
            $d1 = array('s' => 2,'h' => array($use));
            $desc []= $d1;
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

    // University
    if (1)
    {
        $unique_types = array();
        foreach($all_prosonta as $proson)
        {
            $r1 = $proson['row'];
            if ($r1['CLASSID'] != 101 && $r1['CLASSID'] != 102 && $r1['CLASSID'] != 103 && $r1['CLASSID'] != 104) continue;
            
            // Param pid 3 is the tmima
            // Param pid [6,8,7,8] the music eidikeysi
            foreach($proson['params'] as $param)
            {
                $mouseid = 'Θεωρητικά Ευρωπαϊκής Μουσικής';



                // Check position
                if ($posrow && mb_strtolower(RemoveAccents($posrow['DESCRIPTION'])) == mb_strtolower(RemoveAccents($mouseid)))
                    $Has_Uni_For_Position = 1;

                // Them

            }
        }
    }


    // Forced
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
        6,8,12,14       TMS
        8,10,14,16      TMS + Eid for the place
        MAX 36

        Odeio
        Dipl  Org/Orch/Byz/ 4
        Dipl  Orch/Chor/    3
        Pty   Ant/Fug       2
        MAX 18

        Apait Either PT + Eid or Dipl Org

        Proy 
        2*mous + 0.5*gen
        PE79 + 2
        Entop +4
        PP +2
        Sinip +4
        Gamos +2
        Paidia 2,4,8,10
        MAX 40



    */
    
    $score = min(($moria_tpe + $moria_languages),6.0) + min(($moria_conservatoire_instrument + $moria_ptychio_antfug + $moria_diplomasodeiou),18.0) + min($moria_uni,36) + min($moria_k,40);


    return $score;
}


function CalculateScore($uid,$cid,$placeid,$posid,$debug = 0,&$linkssave = array(),$prosononly = 0,&$desc = array(),$forwhichplace = 0,$forwhichpos = 0)
{
    global $rejr,$xmlp,$required_check_level;
    EnsureProsonLoaded();
    $pr = Single("USERS","ID",$uid);
    if (!$pr)
        return -1;
    $contestrow = Single("CONTESTS","ID",$cid); 
    if (!$contestrow)
        return -1;
    if ($contestrow['CLASSID'] == 101)
    {
        return CalculateScoreForMS($uid,$cid,$placeid,$posid,$desc,0);
    }
    if ($contestrow['CLASSID'] == 102)
    {
        return CalculateScoreForMS($uid,$cid,$placeid,$posid,$desc,1);
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
        $s .= sprintf('<td>%s</td>',$c);
        $info = '';
        foreach($d['h'] as $dd)
        {
            $info .= sprintf("%s<br>",$dd['DESCRIPTION']);
        }
        $s .= sprintf('<td>%s</td>',$info);
        $s .= sprintf('<td>%s</td>',$d['s']);
        $s .= sprintf('</tr>');
            $c++;
    }

    $s .= '</tbody>';
    $s .= '</table>';
    return $s;
}
