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
        printf('<button class="button autobutton is-link block is-large" href="proson.php">Προσόντα</button> ');
        printf('<button class="button autobutton is-success block is-large" href="applications.php">Αιτήσεις</button> ');
        $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($ur['ID']));
        while(($r1 = $q1->fetchArray()))
        {
          if ($r1['ROLE'] == ROLE_CHECKER || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-info block  is-large" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  is-large" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI  || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  is-large" href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  is-large" href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  is-large" href="editkena.php">Διόρθωση Κενών</button> ');
          }
          if ($r1['ROLE'] == ROLE_ROLEEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  is-large" href="roleeditor.php">Role Editor</button> ');
          }
          if ($r1['ROLE'] == ROLE_CONTESTVIEWER || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  is-large" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
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
            printf('<button class="button autobutton  is-info block  is-large" href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR)
          {
            printf('<button class="button autobutton  is-primary block  is-large" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CONTESTVIEWER)
          {
            printf('<button class="button autobutton  is-primary block is-large" href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI)
          {
            printf('<button class="button autobutton  is-link block is-large" href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR)
          {
            printf('<button class="button autobutton  is-link block is-large" href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES)
          {
            printf('<button class="button autobutton  is-link block is-large" href="editkena.php">Διόρθωση Κενών</button> ');
          }
          if ($r1['ROLE'] == ROLE_ROLEEDITOR)
          {
            printf('<button class="button autobutton  is-primary block is-large" href="roleeditor.php">Role Editor</button> ');
          }
        }
      }



      printf('<br><br><hr><button class="button autobutton  is-warning block" href="settings.php">Ρυθμίσεις</button> ');
      if ($superadmin)
        printf('<button class="button autobutton  is-danger block" href="superadmin.php">Superadmin</button> ');
      if ($superadmin)
        printf('<button class="button autobutton  is-success block" href="update.php">Update</button> ');
      printf('<br>');
    }
else
{
 
  $_SESSION['return_msa'] = 'mpd';
    ?>


<script src="https://xemantic.github.io/shader-web-background/dist/shader-web-background.min.js"></script>
<?php
echo'
<script type="x-shader/x-fragment" id="Image" >
precision highp float;
uniform vec2  iResolution;
uniform float iTime;
// ... other needed uniforms

// -- Paste your Shadertoy code here:
';
echo file_get_contents("shader.glsl");
echo '
// -- End of Shadertoy code
void main() {
mainImage(gl_FragColor, gl_FragCoord.xy);
}
  </script>';
?>
  <script>
shaderWebBackground.shade({
shaders: {
  Image: {
    uniforms: {
      iResolution: (gl, loc, ctx) => gl.uniform2f(loc, ctx.width, ctx.height),
      iTime:       (gl, loc) => gl.uniform1f(loc, performance.now() / 1000),
    }
  }
}
});

</script>


<nav class="navbar" role="navigation" aria-label="main navigation" style="background-color: transparent !important">

  <div class="navbar-brand">
    <div class="navbar-item">
      <img src="../shde/icon.svg" />
  </div>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasicExample" class="navbar-menu">
    <div class="navbar-start">

<!--    <div class="navbar-item">

    <div class="dropdown is-hoverable">
  <div class="dropdown-trigger">
    <button class="button is-danger" aria-haspopup="true" aria-controls="dropdown-menu4">
      <span>Login</span>
    </button>
  </div>
  <div class="dropdown-menu" id="dropdown-menu4" role="menu">
    <div class="dropdown-content">
    <div class="dropdown-item">
        <p>
          <a href="https://www.msa-apps.com/taxis.php" class="button is-small is-primary block" >Taxis Login</a><br>
          <a href="bio.php?login" class="button is-small is-link block">Biometric Login<hr></a>
        </p>
      </div>
     </div>
    </div>
  </div>
</div>
<a class="navbar-item" href="https://www.youtube.com/watch?v=0U2-Xbg2xMY">
        <button class="button is-link">Video Demo</button>
      </a>
    </div>
-->
    <div class="navbar-end">
      <div class="navbar-item">
        

      
      </div>
    </div>
  </div>
</nav>

        Μητρώο Προσόντων και Διαγωνισμών

        <hr>
        Εξομοίωση login<hr><br><br>
        <table class="table">
        <thead>
          <th>#</th>
          <th>Όνομα</th>
          <th>Θέση</th>
        </thead>
        <tbody>
            <tr><td>1</td><td><a href="auth.php?redirect=index.php&afm2=1001001001">Παπαδόπουλος Νίκος</a></td><td>Υποψήφιος</td></tr>
            <tr><td>2</td><td><a href="auth.php?redirect=index.php&afm2=1001001002">Γεωργίου Βασίλειος</a></td><td>Ελεγτής Επίπεδο 1</td></tr>
            <tr><td>3</td><td><a href="auth.php?redirect=index.php&afm2=1001001003">Νικολάου Παναγιώτης</a></td><td>Κατασκευή Διαγωνισμού</td></tr>
            <tr><td>4</td><td><a href="auth.php?redirect=index.php&afm2=1001001004">Παπάζογλου Μιχάλης</a></td><td>Πανεπιστήμιο που καταχωρεί προσόντα</td></tr>
            <tr><td>5</td><td><a href="auth.php?redirect=index.php&afm2=1001001005">Μαρής Φώτης</a></td><td>Διόρθωση Γενικών Προσόντων</td></tr>
            <tr><td>6</td><td><a href="auth.php?redirect=index.php&afm2=1001001006">Μαρίνου Ευτυχία</a></td><td> Διόρθωση Κενών Μουσικού Αλίμου</td></tr>
            <tr><td>7</td><td><a href="auth.php?redirect=index.php&afm2=1001001007">Πασχαλίδης Ορέστης</a></td><td> Διόρθωση Ρόλων</td></tr>
            <tr><td>8</td><td><a href="auth.php?redirect=index.php&afm2=1001001008">Φουρής Αγαμέμνων</a></td><td>Ελεγτής Επίπεδο 2</td></tr>
            <tr><td>9</td><td><a href="auth.php?redirect=index.php&afm2=1001001009">Ζάxου Γεωργιος</a></td><td>Προβολή Διαγωνισμού</td></tr>
        </tbody>
        </table>

<?php
}


echo '</div>';
