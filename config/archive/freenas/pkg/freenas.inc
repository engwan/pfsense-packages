<?php
/* $Id$ */
/* ========================================================================== */
/*
    freenas.inc
    part of pfSense (http://www.pfSense.com)
    Copyright (C) 2006 Daniel S. Haischt <me@daniel.stefan.haischt.name>
    All rights reserved.

    Based on FreeNAS (http://www.freenas.org)
    Copyright (C) 2005-2006 Olivier Cochard-Labb� <olivier@freenas.org>.
    All rights reserved.

    Based on m0n0wall (http://m0n0.ch/wall)
    Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
    All rights reserved.
                                                                              */
/* ========================================================================== */
/*
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

     1. Redistributions of source code must retain the above copyright notice,
        this list of conditions and the following disclaimer.

     2. Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
                                                                              */
/* ========================================================================== */

require_once("freenas_config.inc");

function sync_package_freenas() {
}

function oninstall_copy_kernel_binaries($action = "install") {
   $binaries = array();
   $binaries[] = array("source" => "/usr/local/pkg/iscsi_initiator.ko",
                       "target" => "/boot/kernel/iscsi_initiator.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/ext2fs.ko",
                       "target" => "/boot/kernel/ext2fs.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/geom_concat.ko",
                       "target" => "/boot/kernel/geom_concat.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/geom_gpt.ko",
                       "target" => "/boot/kernel/geom_gpt.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/geom_mirror.ko",
                       "target" => "/boot/kernel/geom_mirror.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/geom_stripe.ko",
                       "target" => "/boot/kernel/geom_stripe.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/geom_vinum.ko",
                       "target" => "/boot/kernel/geom_vinum.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/ntfs.ko",
                       "target" => "/boot/kernel/ntfs.ko",
                       "backup" => false);
   $binaries[] = array("source" => "/usr/local/pkg/kernel.gz",
                       "target" => "/boot/kernel/kernel.gz",
                       "backup" => true);

   if ($action == "install") {   
     while (list(, $val) = each($binaries)) {
       $static_output .= "FreeNAS: Moving binary file... ";
       update_output_window($static_output);
       if ($val['backup'] && file_exists($val['target']))
         rename($val['target'], "{$val['target']}.org");
       rename($val['source'], $val['target']);
       $static_output .= "done.\n";
       update_output_window($static_output);
     }
   } else if ($action == "deinstall") {
     while (list($source, $target) = each($binaries)) {
       $static_output .= "FreeNAS: Removing binary file... ";
       update_output_window($static_output);
       unlink($target);
       $static_output .= "done.\n";
       update_output_window($static_output);
     }
   }
}

function remove_package_contents($pkg = "") {
  if ($pkg == "") { return; }
  
  $pd = popen("pkg_info -rf {$pkg}", "r");

  if (! pd) { return; }

  while (! feof($pd)) {
    $buffer = fgets($pd, 4096);

    if (strpos($buffer, "File:") !== false) {
      $file = trim(str_replace("File:", "", $buffer));
      
      if (strpos($file, "man/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "share/doc/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "share/aclocal/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "include/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "libdata/pkgconfig/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "info/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "etc/rc.d/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      } else if (strpos($file, "etc/") !== false) {
        if (strpos($file, ".dist") !== false ||
            strpos($file, ".sample") !== false ||
            strpos($file, ".example") !== false ||
            strpos($file, ".default") !== false ||
            strpos($file, ".sh") !== false) {
          unlink_if_exists("/usr/local/{$file}");
        }
      } else if (strpos($file, "share/examples/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      }

      /* What about locales? */
      /*
      } else if (strpos($file, "share/local/") !== false) {
        unlink_if_exists("/usr/local/{$file}");
      }
      */
    }
  }
  
  fclose ($pd);
}

function adjust_package_contents() {
  remove_package_contents("avahi+libdns-0.6.12");
  remove_package_contents("ataidle-0.9");
  remove_package_contents("perl-5.8.8");
  remove_package_contents("libiconv-1.9.2_2");
  remove_package_contents("libxml2-2.6.26");
  remove_package_contents("gettext-0.14.5_2");
  remove_package_contents("glib-2.10.3");
  remove_package_contents("expat-2.0.0_1");
  remove_package_contents("wzdftpd-0.7.2_1");
  remove_package_contents("e2fsprogs-1.39_1");
  remove_package_contents("smartmontools-5.36");
  remove_package_contents("aaccli-1.0");
  remove_package_contents("rsync-2.6.8_2");
  remove_package_contents("scponly-4.6_1");
  remove_package_contents("cracklib-2.7_2");
  remove_package_contents("samba-3.0.23,1");
  remove_package_contents("netatalk-2.0.3_3,1");
  remove_package_contents("pam_ldap-1.8.2");
}

function oninstall_copy_misc_binaries($action = "install") {
   $binaries = array();
   $binaries[] = array("source" => "/usr/local/pkg/iscontrol",
                       "target" => "/usr/local/sbin/iscontrol");
   $binaries[] = array("source" => "/usr/local/pkg/nfsd",
                       "target" => "/usr/sbin/nfsd");
   $binaries[] = array("source" => "/usr/local/pkg/nfsd",
                       "target" => "/usr/sbin/mountd");
   $binaries[] = array("source" => "/usr/local/pkg/rpcbind",
                       "target" => "/usr/sbin/rpcbind");
   $binaries[] = array("source" => "/usr/local/pkg/rpc.lockd",
                       "target" => "/usr/sbin/rpc.lockd");
   $binaries[] = array("source" => "/usr/local/pkg/rpc.lockd",
                       "target" => "/usr/sbin/rpc.lockd");
                       
   if ($action == "install") {
     while (list(, $val) = each($binaries)) {
       $static_output .= "FreeNAS: Installing binary file... ";
       update_output_window($static_output);
       mwexec("install -s {$source} ${target}");
       $static_output .= "done.\n";
       update_output_window($static_output);
     }
   } else if ($action == "deinstall") {
     while (list($val['source'], $val['target']) = each($binaries)) {
       $static_output .= "FreeNAS: Removing binary file... ";
       update_output_window($static_output);
       unlink($target);
       $static_output .= "done.\n";
       update_output_window($static_output);
     }
   }
}

function oninstall_setup_config() {
  global $config;
  
  $config['system']['zeroconf_disable'] = "yes";
  
  $freenas_config =& $config['installedpackages']['freenas']['config'][0];
  
  if (!is_array($freenas_config['disks']))
    $freenas_config['disks'] = array();
  if (!is_array($freenas_config['mounts']))
    $freenas_config['mounts'] = array();
    
  if (!is_array($freenas_config['samba']))
    $freenas_config['samba'] = array();    
  $freenas_config['samba']['netbiosname'] = "freenas";
  $freenas_config['samba']['workgroup'] = "WORKGROUP";
  $freenas_config['samba']['serverdesc'] = "pfSense Server";
  $freenas_config['samba']['security'] = "share";
  $freenas_config['samba']['localmaster'] = "yes";
  
  if (!is_array($freenas_config['ftp']))
    $freenas_config['ftp'] = array();
  $freenas_config['ftp']['numberclients'] = "5";
  $freenas_config['ftp']['maxconperip'] = "2";
  $freenas_config['ftp']['timeout'] = "300";
  $freenas_config['ftp']['port'] = "21";
  $freenas_config['ftp']['anonymous'] = "yes";
  $freenas_config['ftp']['pasv_max_port'] = "0";
  $freenas_config['ftp']['pasv_min_port'] = "0";
  
  if (!is_array($freenas_config['afp']))
    $freenas_config['afp'] = array();
    
  if (!is_array($freenas_config['rsyncd']))
    $freenas_config['rsyncd'] = array();
  $freenas_config['rsyncd']['readonly'] = "no";
  $freenas_config['rsyncd']['port'] = "873";
  
  if (!is_array($freenas_config['nfs']))
    $freenas_config['nfs'] = array();
}

function custom_php_install_command() {
  global $g;

  oninstall_copy_kernel_binaries();
  oninstall_copy_misc_binaries();
  adjust_package_contents();
  oninstall_setup_config();
  
  if (! file_exists("/usr/local/etc/rc.d"))
    mwexec("mkdir -p /usr/local/etc/rc.d");

  if (! file_exists("{$g['varrun_path']}/dbus"))
    mkdir("{$g['varrun_path']}/dbus");

  if (! file_exists("{$g['varrun_path']}/avahi-daemon"))
    mkdir("{$g['varrun_path']}/avahi-daemon");

  rename("/usr/local/pkg/rc.freenas", "/usr/local/etc/rc.d/freenas.sh");
  chmod ("/usr/local/etc/rc.d/freenas.sh", 0755);

  mwexec("ln -s /usr/local/etc/uams/uams_passwd.so /usr/local/etc/uams/uams_clrtxt.so");
  mwexec("ln -s /usr/local/etc/uams/uams_dhx_passwd.so /usr/local/etc/uams/uams_dhx.so");
  /* hack cause I linked netatalk against db44 instead of db42 */
  mwexec("ln -s /usr/local/lib/libdb-4.4.so.0 /usr/local/lib/libdb-4.2.so.2");
}

function custom_php_deinstall_command() {
  global $config, $g;

  oninstall_copy_kernel_binaries("deinstall");
  oninstall_copy_misc_binaries("deinstall");

  if (! file_exists("/usr/local/etc/rc.d"))
    mwexec("rm -rf /usr/local/etc/rc.d");

  if (! file_exists("{$g['varrun_path']}/dbus"))
    mwexec("rm -rf {$g['varrun_path']}/dbus");

  if (! file_exists("{$g['varrun_path']}/avahi-daemon"))
    mwexec("rm -rf {$g['varrun_path']}/avahi-daemon");

  unlink_if_exists("/usr/local/etc/uams/uams_clrtxt.so");
  unlink_if_exists("/usr/local/etc/uams/uams_dhx.so");
  unlink_if_exists("/usr/local/etc/rc.d/freenas.sh");
  unlink_if_exists("/usr/local/lib/libdb-4.2.so.2");

  unset($config['installedpackages']['freenas']);
  write_config();
  conf_mount_ro();
}
?>
