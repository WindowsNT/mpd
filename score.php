<?php


// Scores for metathesi music schools algorithm for example

function CalculateScoreForMS($uid,$cid,$placeid,$posid,&$desc = array(),$typems = 0)
{
    // Music Schools Calculator
}


/*


    $desc = array of results
        $item = array of ['s'] => score and ['h'] => row of proson used

*/

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
    if ($contestrow['CLASSID'] != 0)
    {

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
        if((int)$d['s'] == 0 && $onlypos)    
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
