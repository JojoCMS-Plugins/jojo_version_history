<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2009 Harvey Kane <code@ragepank.com>
 * Copyright 2009 Michael Holt <code@gardyneholt.co.nz>

 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Mike Cochrane <mikec+jojo@mikenz.geek.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_core
 */

class Jojo_Field_Version extends Jojo_Field
{
    /**
     * Do serverside error checking
     */
    function checkValue()
    {
        $this->value++;
    }

    function afterSave()
    {
        /* Log in version history */
        global $_USERID;

        /* Get the new row */
        $query = sprintf('SELECT * FROM {%s} WHERE `%s` = ? LIMIT 1', $this->table->getTableName(), $this->table->getOption('primarykey'));
        $newRow = Jojo::selectRow($query, $this->table->getRecordID());

        /* Save the new row and other stuff into the version history table */
        $query = 'INSERT INTO {versionhistory} SET
                            `table` = ?,
                            `recordid` = ?,
                            `version` = ?,
                            `user` = ?,
                            `date` = NOW(),
                            `data` = ?,
                            `ip` = ?,
                            `uri` = ?,
                            `referer` = ?,
                            `browser` = ?';
       Jojo::insertQuery($query, array(
                              $this->table->getTableName(),
                              $this->table->getRecordID(),
                              $this->value,
                              $_USERID ? $_USERID : 0,
                              serialize($newRow),
                              Jojo::getIp(),
                              defined('_FULLSITEURI') ? _FULLSITEURI : '',
                              isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                              Jojo::getBrowser()));
    }


    function displayEdit()
    {
        global $smarty;
        $smarty->assign('value', $this->value);

        /* Create the Diff object. */
        $dir = realpath(dirname(__FILE__) . '/../../../');
        require_once $dir . '/external/text_diff/Diff.php';
        require_once $dir . '/external/text_diff/Diff/Renderer.php';
        require_once $dir . '/external/text_diff/Diff/Renderer/inline.php';
        $renderer = new Text_Diff_Renderer_inline();

        $maxversions = 21;

        $fieldNames = Jojo::selectAssoc('SELECT fd_field, fd_name FROM {fielddata} WHERE fd_table = ?', $this->table->getTableName());
        $hiddenFields = Jojo::selectAssoc('SELECT fd_field, fd_name FROM {fielddata} WHERE fd_table = ? AND fd_type = "hidden"', $this->table->getTableName());

        $query = "SELECT `table`, `version`, `date`, `us_login` as user, data FROM {versionhistory} as versionhistory LEFT JOIN {user} as user ON (user.userid = versionhistory.user) WHERE `table` = ? AND `recordid` = ? ORDER BY `version` DESC LIMIT ?";
        $values = array($this->table->getTableName(), $this->table->getRecordID(), $maxversions);
        $revisions = Jojo::selectQuery($query, $values);

        foreach($revisions as $i => $v) {
            $current = unserialize($revisions[$i]['data']);
            $previous = isset($revisions[$i + 1]) ? unserialize($revisions[$i + 1]['data']) : array();

            /* Work out which columns changed between versions */
            $changes = array();
            foreach ($current as $k => $v) {
                if ($k == $this->fd_field || isset($hiddenFields[$k])) {
                    /* Skip the version field as this will always change, and hidden fields */
                    continue;
                }
                if (isset($current[$k]) && isset($previous[$k]) && $current[$k] != $previous[$k]) {
                    $current[$k] = str_replace("\r", "", $current[$k]);
                    $new = preg_split("/[\n\r]/", $current[$k]);
                    $previous[$k] = str_replace("\r", "", $previous[$k]);
                    $old = preg_split("/[\n\r]/", $previous[$k]);
                    $changes[$fieldNames[$k]] = $renderer->render(new Text_Diff('auto', array($old, $new)));
                }
            }
            $revisions[$i]['changelog'] = $changes;
        }
        $smarty->assign('revisions', $revisions);

        return $smarty->fetch('admin/fields/version.tpl');;
    }
}
