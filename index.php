<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
if (array_key_exists("target_id",$_SESSION))
  unset($_SESSION['target_id']);
if ($afm && $ur)
    {
        PrintHeader();
        echo '<div class="content" style="margin: 20px">';


        printf('<button class="button autobutton is-link block " href="proson.php">Προσόντα</button> ');
        printf('<button class="button autobutton is-success block " href="applications.php">Αιτήσεις</button> ');
        $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($ur['ID']));
        while(($r1 = $q1->fetchArray()))
        {
          if ($r1['ROLE'] == ROLE_CHECKER || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-info block  " href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_CREATOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  " href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI  || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  " href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_GLOBALPROSONEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  " href="globaleditor.php">Διόρθωση Προσόνων</button> ');
          }
          if ($r1['ROLE'] == ROLE_FOREASSETPLACES || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-link block  " href="editkena.php">Διόρθωση Κενών</button> ');
          }
          if ($r1['ROLE'] == ROLE_ROLEEDITOR || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  " href="roleeditor.php">Role Editor</button> ');
          }
          if ($r1['ROLE'] == ROLE_CONTESTVIEWER || $r1['ROLE'] == ROLE_SUPERADMIN)
          {
            printf('<button class="button autobutton  is-primary block  " href="contest.php">Διαγωνισμοί</button> ',$r1['ID']);
          }
          if ($superadmin)
            break;
      }

      if ($superadmin)
      {
        $q1 = QQ("SELECT * FROM ROLES");
        printf('<button class="button autobutton  is-primary block  " href="contest.php">Διαγωνισμοί</button> ');
        printf('<button class="button autobutton  is-link block " href="globaleditor.php">Διόρθωση Προσόνων</button> ');
        printf('<button class="button autobutton  is-link block " href="editkena.php">Διόρθωση Κενών</button> ');
        printf('<button class="button autobutton  is-primary block " href="roleeditor.php">Role Editor</button> ');
        while(($r1 = $q1->fetchArray()))
        {
          if ($r1['ROLE'] == ROLE_CHECKER)
          {
            printf('<button class="button autobutton  is-info block  " href="check.php?t=%s">Έλεγχος Προσόντων Ομάδας %s</button> ',$r1['ID'],$r1['ID']);
          }
          if ($r1['ROLE'] == ROLE_UNI)
          {
            printf('<button class="button autobutton  is-link block " href="provider.php?t=%s">Ίδρυμα Ομάδα %s</button> ',$r1['ID'],$r1['ID']);
          }
        }
      }



    }
else
{
 
  echo '<div class="content" style="margin: 20px">';
  $_SESSION['return_ministry_login'] = 'mpd';
  $_SESSION['return_msa'] = 'mpd';
  $_SESSION['return_psd_login'] = "mpd";

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
      <img src="icon.svg" />
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

    <div class="navbar-item">

    <?php
  if ($test_users == 0)
  {
  ?>
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
          <a href="<?= $taxis_login ?>" class="button is-small is-primary block" >Taxis Login</a>
          <a href="<?= $psd_login ?>" class="button is-small is-primary block" >ΠΣΔ Login</a>
          <a href="locallogin.php" class="button is-small is-primary block" >Τοπικό Login</a>
          <a href="bio.php?login" class="button is-small is-link block">Biometric Login</a>
        </p>
      </div>
     </div>
    </div>
  </div>
  <?php
  }
  ?>
</div>
<?php
if ($test_users == 1)
{
  ?>
<a class="navbar-item" href="https://www.youtube.com/watch?v=0U2-Xbg2xMY">
        <button class="button is-link">Video Demo</button>
      </a>
      <?php
}
?>
    </div>

    <div class="navbar-end">
      <div class="navbar-item">
        

      
      </div>
    </div>
  </div>
</nav>

        Μητρώο Προσόντων και Διαγωνισμών

        <hr>
        <?php
        if ($test_users)
        {
        ?>
        <b>Δοκιμαστικά login</b><hr>
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
        ?>

<?php
}


echo '</div>';
