<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';
printf('Μητρώο Προσόντων και Διαγωνισμών');
if (array_key_exists("target_id",$_SESSION))
  unset($_SESSION['target_id']);
if ($afm && $ur)
    {
        printf(' %s %s - %s<hr>',$ur['LASTNAME'],$ur['FIRSTNAME'],$afm);
        printf('<button class="button is-danger autobutton" href="auth.php?redirect=index.php&logout=1">Logout</button><hr>');
        printf('<button class="button autobutton is-link" href="proson.php">Προσόντα</button> ');
        printf('<button class="button autobutton is-success" href="applications.php">Αιτήσεις</button> ');
        $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($ur['ID']));
        while($r1 = $q1->fetchArray())
        {
          if ($r1['ROLE'] == ROLE_CHECKER)
          {
            printf('<button class="button autobutton  is-info" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR)
          {
            printf('<button class="button autobutton  is-primary" href="contest.php?t=%s">Διαγωνισμοί Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI)
          {
            printf('<button class="button autobutton  is-link" href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR)
          {
            printf('<button class="button autobutton  is-link" href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES)
          {
            printf('<button class="button autobutton  is-link" href="editkena.php">Διόρθωση Κενών</button> ');
          }
      }
    }
else
{
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
          <a href="auth.php?redirect=index.php&afm2=1001001001">Παπαδόπουλος Νίκος ΑΦΜ 1001001001<hr></a>
          <a href="auth.php?redirect=index.php&afm2=1001001002">Γεωργίου Βασίλειος Νίκος ΑΦΜ 1001001002 Ελεγτής<hr></a>
          <a href="auth.php?redirect=index.php&afm2=1001001004">Παπάζογλου Μιχάλης ΑΦΜ 1001001004 Πανεπιστήμιο<hr></a>
          <a href="auth.php?redirect=index.php&afm2=1001001003">Νικολάου Παναγιώτης ΑΦΜ 1001001003 Κατασκευή Διαγωνισμού<hr></a>
          <a href="auth.php?redirect=index.php&afm2=1001001005">Μαρής Φώτης ΑΦΜ 1001001004 Διόρθωση Γενικών Προσόντων<hr></a>
          <a href="auth.php?redirect=index.php&afm2=1001001006">Μαρίνου Ευτυχία ΑΦΜ 1001001005 Διόρθωση Κενών Μουσικού Αλίμου<hr></a>
        </p>
      </div>
    </div>
  </div>
</div>
    
<?php
}
echo '</div>';
