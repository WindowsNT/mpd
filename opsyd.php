<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';

if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }


if (!HasContestAccess($req['cid'],$ur['ID'],1))
    die;

$f = $req['f'];

$kena = <<<LEIT
ΠΕ79.01 / ΤΕ16,Μουσικό Γυμνάσιο Παλλήνης,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Γυμνάσιο Παλλήνης,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Γυμνάσιο Παλλήνης,Ταμπουράς,4
ΠΕ79.01 / ΤΕ16,Μουσικό Λύκειο Παλλήνης,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αγρινίου,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αγρινίου,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αγρινίου,Κλαρινέτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αγρινίου,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αγρινίου,Ταμπουράς,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αθηνών,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αθηνών,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αθηνών,Λύρα Πολίτικη,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αθηνών,Ταμπουράς,5
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Ακορντεόν,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Γκάιντα,6
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλεξανδρούπολης,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Θεωρητικά Ευρωπαϊκής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Όμποε,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Ούτι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Παραδοσιακό Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Ταμπουράς,10
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αλίμου,Τρομπόνι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Λύρα Ποντιακή,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Παραδοσιακό Κλαρίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμύνταιου,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Παραδοσιακό Κλαρίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αμφισσας,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Κανονάκι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Παραδοσιακό Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αργολίδας,Ταμπουράς,5
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αρτας,Ακορντεόν,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αρτας,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αρτας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αρτας,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Αρτας,Ταμπουράς,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βαρθολομιού,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βαρθολομιού,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βέροιας,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Κανονάκι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Κιθάρα ηλεκτρική,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Πιάνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Σαντούρι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Βόλου,Ταμπουράς,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Γιαννιτσών,Ακορντεόν,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Γιαννιτσών,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Βιόλα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Λύρα Ποντιακή,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Πιάνο,5
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Ταμπουράς,6
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δράμας,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δυτικής Λέσβου,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Δυτικής Λέσβου,Σαντούρι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ζακύνθου,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ζακύνθου,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ζακύνθου,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ζακύνθου,Μαντολίνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηγουμενίτσας,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηγουμενίτσας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηγουμενίτσας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηγουμενίτσας,Παραδοσιακό Κλαρίνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηγουμενίτσας,Πιάνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Μαντολίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ηρακλείου,Ταμπουράς,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Κλαρινέτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Λύρα Κρητική,7
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Παραδοσιακό Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Πιάνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θέρισου,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θεσσαλονίκης,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Θεσσαλονίκης,Ταμπουράς,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ίλιου,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ίλιου,Ταμπουράς,5
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ιωαννίνων,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ιωαννίνων,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καβάλας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καβάλας,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καβάλας,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καβάλας,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καβάλας,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Κλαρινέτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Παραδοσιακό Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καλαμάτας,Ταμπουράς,8
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Θεωρητικά Ευρωπαϊκής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καρδίτσας,Ταμπουράς,6
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Θεωρητικά Ευρωπαϊκής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Λύρα Ποντιακή,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Παραδοσιακό Κλαρίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Καστοριάς,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κατερίνης,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κατερίνης,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κατερίνης,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κατερίνης,Νέι - Καβάλι - Παραδοσιακοί Αυλοί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κατερίνης,Ταμπουράς,11
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κέρκυρας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κέρκυρας,Μαντολίνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κέρκυρας,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Ούτι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Παραδοσιακό Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κομοτηνής,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κορίνθου,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κορίνθου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κορίνθου,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Κορίνθου,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Ακορντεόν,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Θεωρητικά Βυζαντινής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Κιθάρα κλασική,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Παραδοσιακό Κλαρίνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Ταμπουράς,7
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λαμίας,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λάρισας,Κανονάκι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λάρισας,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λάρισας,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λάρισας,Ταμπουράς,8
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Θεωρητικά Ευρωπαϊκής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Μαντολίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λασιθίου,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Κλαρινέτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Κόρνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Μαντολίνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λευκάδας,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λιβαδειάς,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λιβαδειάς,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λιβαδειάς,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Λιβαδειάς,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Πιάνο,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Σαντούρι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Μυτιλήνης,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ξάνθης,Ακορντεόν,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ξάνθης,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ξάνθης,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ξάνθης,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ξάνθης,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πάτρας,Θεωρητικά Ευρωπαϊκής Μουσικής,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πάτρας,Μαντολίνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πάτρας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πειραιά,Κοντραμπάσο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πειραιά,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πειραιά,Ταμπουράς,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Θεωρητικά Ευρωπαϊκής Μουσικής,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πρέβεζας,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πτολεμαΐδας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Πτολεμαΐδας,Ταμπουράς,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Λύρα Κρητική,4
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρεθύμνου,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Ακορντεόν,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Λύρα Δωδεκανήσου,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Παραδοσιακό Βιολί,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Ρόδου,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Κλαρινέτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σάμου,Ταμπουράς,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σερρών,Διεύθυνση Ορχήστρας,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σερρών,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σερρών,Λύρα Ποντιακή,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σερρών,Ταμπουράς,8
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σιάτιστας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σιάτιστας,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σιάτιστας,Πιάνο,3
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σιάτιστας,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σιάτιστας,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Κανονάκι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Σπάρτης,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Ταμπουράς,5
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τρικάλων,Τρομπόνι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Κιθάρα ηλεκτρική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Κιθάρα κλασική,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Κρουστά Ευρωπαϊκά (Κλασικά- Σύγχρονα),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Μπάσο ηλεκτρικό,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Μπουζούκι (Τρίχορδο),1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Παραδοσιακό Κλαρίνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Σαντούρι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Ταμπουράς,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Τρομπέτα,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Τριπόλεως,Τρομπόνι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Βιολί,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Βιολοντσέλο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Θεωρητικά Βυζαντινής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Θεωρητικά Ευρωπαϊκής Μουσικής,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Λαούτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Πιάνο,2
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χαλκίδας,Φλάουτο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Κανονάκι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Ούτι,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Παραδοσιακά Κρουστά,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Πιάνο,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Ταμπουράς,1
ΠΕ79.01 / ΤΕ16,Μουσικό Σχολείο Χίου,Τρομπέτα,1
LEIT;

if ($f == 1)
{
    $lines = explode("\n",$kena);
    QQ("BEGIN TRANSACTION");
    foreach($lines as $line)
    {
         $items = str_getcsv($line, ",", "\"");
         if (count($items) != 4)
            continue;
        $placeid = QQ("SELECT * FROM PLACES WHERE CID = ? AND DESCRIPTION = ?",array($req['cid'],$items[1]))->fetchArray();
        $pid = 0;
        if (!$placeid)
        {
            QQ("INSERT INTO PLACES (CID,PARENTPLACEID,DESCRIPTION) VALUES(?,?,?)",array(
                $req['cid'],0,$items[1]
            ));
            $pid = $lastRowID;
            printf("Added %s <br>",$items[1]);
        }
        else
            $pid = $placeid['ID'];

        $position = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ? AND DESCRIPTION = ?",array($req['cid'],$pid,$items[2]))->fetchArray();
        if (!$position)
        {
            QQ("INSERT INTO POSITIONS (CID,PLACEID,DESCRIPTION,COUNT) VALUES(?,?,?,?)",array(
                $req['cid'],$pid,$items[2],$items[3]
            ));
            printf("Added %s <br>",$items[1]);
        }
    }
    QQ("COMMIT");    
    die("OK");
}

if ($f == 2)
{
    $re = QQ("SELECT * FROM POSITIONGROUPS WHERE CID = ?",array($req['cid']))->fetchArray();
    if ($re)
        die("EXISTS");

    $r0 = QQ("SELECT * FROM POSITIONGROUPS WHERE CID = ?",array($req['from']))->fetchArray();
    if (!$r0)
        die("ERR");

    QQ("BEGIN TRANSACTION");

    // copy prosonta thesewn
    QQ("INSERT INTO POSITIONGROUPS (CID,GROUPLIST) VALUES(?,?)",array(
        $req['cid'],$r0['GROUPLIST']
    ));
    
    $q2 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND FORTHESI IS NOT NULL AND FORTHESI != ''",array($req['from']));
    $dups = array();
    while($r1 = $q2->fetchArray())
    {
        QQ("INSERT INTO REQS2 (CID,PLACEID,POSID,FORTHESI,PROSONTYPE,SCORE,REGEXRESTRICTIONS) VALUES(?,?,?,?,?,?,?)",array($req['cid'],0,0,$r1['FORTHESI'],$r1['PROSONTYPE'],$r1['SCORE'],$r1['REGEXRESTRICTIONS']));
        $dups[$r1['ID']] = $lastRowID;
    }
    foreach($dups as $k => $newid)
    {
        $old_row = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($k))->fetchArray();
        if (!$old_row || !$newid)
            continue;

        $neworlink = 0;
        $newnotlink = 0;
        $newandlink = 0;
        if ((int)$old_row['ORLINK'] != 0)
                $neworlink = $dups[(int)$old_row['ORLINK']];
        if ((int)$old_row['NOTLINK'] != 0)
                $newnotlink = $dups[(int)$old_row['NOTLINK']];
        if ((int)$old_row['ANDLINK'] != 0)
                $newandlink = $dups[(int)$old_row['ANDLINK']];
        QQ("UPDATE REQS2 SET ORLINK = ?,NOTLINK = ?,ANDLINK = ? WHERE ID = ?",array($neworlink,$newnotlink,$newandlink,$newid));
    }
    

    QQ("COMMIT");
    die("OK");
}

// copy prosonta diagwnismou
if ($f == 3)
{
    QQ("BEGIN TRANSACTION");
    QQ("DELETE FROM REQS2 WHERE CID = ? AND POSID = 0 AND PLACEID = 0 AND (FORTHESI IS NULL OR FORTHESI == '')",array($req['cid']));
    $q2 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND POSID = 0 AND PLACEID = 0 AND (FORTHESI IS NULL OR FORTHESI == '')",array($req['from']));
    $dups = array();
    while($r1 = $q2->fetchArray())
    {
        QQ("INSERT INTO REQS2 (CID,PLACEID,POSID,PROSONTYPE,SCORE,REGEXRESTRICTIONS) VALUES(?,?,?,?,?,?)",array($req['cid'],0,0,$r1['PROSONTYPE'],$r1['SCORE'],$r1['REGEXRESTRICTIONS']));
        $dups[$r1['ID']] = $lastRowID;
    }
    foreach($dups as $k => $newid)
    {
        $old_row = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($k))->fetchArray();
        if (!$old_row || !$newid)
            continue;

        $neworlink = 0;
        $newnotlink = 0;
        $newandlink = 0;
        if ((int)$old_row['ORLINK'] != 0)
                $neworlink = $dups[(int)$old_row['ORLINK']];
        if ((int)$old_row['NOTLINK'] != 0)
                $newnotlink = $dups[(int)$old_row['NOTLINK']];
        if ((int)$old_row['ANDLINK'] != 0)
                $newandlink = $dups[(int)$old_row['ANDLINK']];
        QQ("UPDATE REQS2 SET ORLINK = ?,NOTLINK = ?,ANDLINK = ? WHERE ID = ?",array($neworlink,$newnotlink,$newandlink,$newid));
    }
    

    QQ("COMMIT");
    die("OK");
}

// copy prosonta forewn
if ($f == 4)
{
    QQ("BEGIN TRANSACTION");
    xdebug_break();
    QQ("DELETE FROM REQS2 WHERE CID = ? AND POSID = 0 AND PLACEID != 0 AND (FORTHESI IS NULL OR FORTHESI == '')",array($req['cid']));
    $q2 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND POSID = 0 AND PLACEID != 0 AND (FORTHESI IS NULL OR FORTHESI == '')",array($req['from']));
    $dups = array();
    while($r1 = $q2->fetchArray())
    {
        // Find new forea id
        $old_foreas = QQ("SELECT * FROM PLACES WHERE ID = ?",array($r1['PLACEID']))->fetchArray();
        if (!$old_foreas)
            continue;
        $new_foreas = QQ("SELECT * FROM PLACES WHERE DESCRIPTION = ? AND CID = ?",array($old_foreas['DESCRIPTION'],$req['cid']))->fetchArray();
        if (!$new_foreas)
            continue;
        
        QQ("INSERT INTO REQS2 (CID,PLACEID,POSID,PROSONTYPE,SCORE,REGEXRESTRICTIONS) VALUES(?,?,?,?,?,?)",array($req['cid'],$new_foreas['ID'],0,$r1['PROSONTYPE'],$r1['SCORE'],$r1['REGEXRESTRICTIONS']));
        $dups[$r1['ID']] = $lastRowID;
    }
    foreach($dups as $k => $newid)
    {
        $old_row = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($k))->fetchArray();
        if (!$old_row || !$newid)
            continue;

        $neworlink = 0;
        $newnotlink = 0;
        $newandlink = 0;
        if ((int)$old_row['ORLINK'] != 0)
                $neworlink = $dups[(int)$old_row['ORLINK']];
        if ((int)$old_row['NOTLINK'] != 0)
                $newnotlink = $dups[(int)$old_row['NOTLINK']];
        if ((int)$old_row['ANDLINK'] != 0)
                $newandlink = $dups[(int)$old_row['ANDLINK']];
        QQ("UPDATE REQS2 SET ORLINK = ?,NOTLINK = ?,ANDLINK = ? WHERE ID = ?",array($neworlink,$newnotlink,$newandlink,$newid));
    }
    

    QQ("COMMIT");
    die("OK");
}
