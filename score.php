<?php

function CalculateScore($uid,$cid,$placeid,$posid,$debug = 0)
{
    global $rejr,$xmlp,$required_check_level;
    EnsureProsonLoaded();
    $pr = QQ("SELECT * FROM USERS WHERE ID = ?",array($uid))->fetchArray();
    if (!$pr)
        return -1;
    $contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray();
    if (!$contestrow)
        return -1;
    $posr = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($posid))->fetchArray();
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

            $has = ProsonResolutAndOrNot($uid,$r1['ID'],$checked,$deep,$reason);

            if ($has != 1)
            {
                if ($sp > 0 || $wouldeval == 1 || $deep >= $min_needed)
                    break; // not required
                $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
                $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s %s x%s',$rootc->attributes()['t'],$reason,$min_needed);
                return -1;    
            }

            // He has it, 
            if ($wouldeval)
            {
                $sp = $r1['SCORE'];
                $deeps = $deep;
                $qpr = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE >= ?",array($uid,$r1['PROSONTYPE'],$required_check_level));
                while($rpr = $qpr->fetchArray())
                {
                    if ($deeps > 0)
                    {
                        $deeps--;
                        continue;
                    }
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
                        $sp = eval2($sp);
            }

        if ($debug)
            {
                if ($sp > 0)
                    printf("%s: %s<br>",$rootc->attributes()['t'],$sp);
            }
        $score += $sp;
        }

        
    }


    if ($posid)
    {
        $v = CalculateScore($uid,$cid,$placeid,0,$debug);;
        if ($v == -1)
            return -1;
        $score += $v;
    }
    else
    if ($placeid)
    {
        $v =  CalculateScore($uid,$cid,0,0,$debug);
        if ($v == -1)
            return -1;
        $score += $v;
    }

    return $score;

}