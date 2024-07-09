<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';
if (array_key_exists("target_id",$_SESSION))
  unset($_SESSION['target_id']);
if ($afm && $ur)
    {
        printf('<button class="button is-danger autobutton block" href="auth.php?redirect=index.php&logout=1">Logout %s %s - %s ID %s</button><hr>',$ur['LASTNAME'],$ur['FIRSTNAME'],$afm,$ur['ID']);
        printf('<button class="button autobutton is-link block" href="proson.php">Προσόντα</button> ');
        printf('<button class="button autobutton is-success block" href="applications.php">Αιτήσεις</button> ');
        $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($ur['ID']));
        while(($r1 = $q1->fetchArray()))
        {
          if ($r1['ROLE'] == ROLE_CHECKER || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-info block" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI  || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block" href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block" href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block" href="editkena.php">Διόρθωση Κενών</button> ');
          }
          if ($r1['ROLE'] == ROLE_ROLEEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block" href="roleeditor.php">Role Editor</button> ');
          }
          if ($superadmin)
            break;
      }

      if ($superadmin)
      {
        $q1 = QQ("SELECT * FROM ROLES");
        while(($r1 = $q1->fetchArray()))
        {
          if ($r1['ROLE'] == ROLE_CHECKER)
          {
            printf('<button class="button autobutton  is-info block" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR)
          {
            printf('<button class="button autobutton  is-primary block" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI)
          {
            printf('<button class="button autobutton  is-link block" href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR)
          {
            printf('<button class="button autobutton  is-link block" href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES)
          {
            printf('<button class="button autobutton  is-link block" href="editkena.php">Διόρθωση Κενών</button> ');
          }
          if ($r1['ROLE'] == ROLE_ROLEEDITOR)
          {
            printf('<button class="button autobutton  is-primary block" href="roleeditor.php">Role Editor</button> ');
          }
        }
      }


      printf('<button class="button autobutton  is-warning block" href="settings.php">Ρυθμίσεις</button> ');
      if ($superadmin)
        printf('<button class="button autobutton  is-danger block" href="superadmin.php">Superadmin</button> ');
      if ($superadmin)
        printf('<button class="button autobutton  is-success block" href="update.php">Update</button> ');
      printf('<br>');
    }
else
{
  printf('Μητρώο Προσόντων και Διαγωνισμών');
  $_SESSION['return_msa'] = 'mpd';
    ?>
  <br>
  <div class="dropdown is-hoverable">
  <div class="dropdown-trigger">
    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu4">
      <span>Login</span>
      <span class="icon is-small">
        <i class="fas fa-angle-down" aria-hidden="true"></i>
      </span>
    </button>
  </div>
  <div class="dropdown-menu" id="dropdown-menu4" role="menu">
    <div class="dropdown-content">
    <div class="dropdown-item">
        <p>
          <a href="https://www.msa-apps.com/taxis.php">Taxis Login<hr></a>
        </p>
      </div>
      <div class="dropdown-item">
        <p>
          <a href="auth.php?redirect=index.php&afm2=1001001001">Παπαδόπουλος Νίκος Υποψήφιος<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001002">Γεωργίου Βασίλειος Ελεγτής Επ 1<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001008">Φουρής Αγαμέμνων  Ελεγτής Επ 2<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001004">Παπάζογλου Μιχάλης Πανεπιστήμιο που καταχωρεί προσόντα<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001003">Νικολάου Παναγιώτης  Κατασκευή Διαγωνισμού<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001005">Μαρής Φώτης  Διόρθωση Γενικών Προσόντων<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001006">Μαρίνου Ευτυχία Διόρθωση Κενών Μουσικού Αλίμου<br><br></a>
          <a href="auth.php?redirect=index.php&afm2=1001001007">Πασχαλίδης Ορέστης Διόρθωση Ρόλων<br><br></a>
        </p>
      </div>
      
    </div>
  </div>
</div>
    
<?php
}
echo '</div>';
