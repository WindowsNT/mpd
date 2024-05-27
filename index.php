<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';
printf('Μητρώο Προσόντων');
if ($afm && $ur)
    {
        printf(' %s %s - %s<hr>',$ur['LASTNAME'],$ur['FIRSTNAME'],$afm);
        printf('<button class="button is-danger autobutton" href="auth.php?redirect=index.php&logout=1">Logout</button><hr>');
        printf('<button class="button autobutton is-link" href="proson.php">Προσόντα</button> ');
        printf('<button class="button autobutton is-success" href="applications.php">Αιτήσεις</button> ');
        $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($ur['ID']));
        while($r1 = $q1->fetchArray())
        {
          if ($r1['ROLE'] == 1)
          {
            printf('<button class="button autobutton  is-info" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == 2)
          {
            printf('<button class="button autobutton  is-primary" href="contest.php?t=%s">Διαγωνισμοί Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
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
          <a href="auth.php?redirect=index.php&afm2=1001001003">Νικολάου Παναγιώτης ΑΦΜ 1001001003 Κατασκευή Διαγωνισμού<hr></a>
        </p>
      </div>
    </div>
  </div>
</div>
    
<?php
}
echo '</div>';
