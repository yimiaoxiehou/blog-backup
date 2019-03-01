<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>
<div class="main">
    <div class="body body-950">
        <?php include 'GoLinks/page-title.php'; ?>
        <div class="container typecho-page-main">
            <div class="column-24 start-01 typecho-list">
                <div class="typecho-list-operate">
                    <form action="<?php $options->index('/action/golinks?add'); ?>" method="post" >
                         <div>&nbsp;&nbsp;&nbsp;&nbsp;KEY:<input name="key" id="key" type="text" value="" />&nbsp;&nbsp;&nbsp;&nbsp;
                          目标:<input name="target" id="target" type="text" value="http://" />
                          <input type="submit" value="添加" />                          
                         </div>
                     </form>
                </div>

               
                <table class="typecho-list-table">
                    <colgroup>
                        <col width="10"/>
                        <col width="280"/>
                        <col width="260"/>
                        <col width="50"/>
                        <col width="100"/>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="typecho-radius-topleft"> </th>
                            <th><?php _e('KEY'); ?></th>
                            <th><?php _e('目标'); ?> </th>
                            <th><?php _e('统计'); ?> </th>
                            <th class="typecho-radius-topright"><?php _e('操作'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="typecho-radius-bottomleft"> </th>
                            <th><?php _e('KEY'); ?></th>
                            <th><?php _e('目标'); ?> </th>
                            <th><?php _e('统计'); ?> </th>   
                            <th class="typecho-radius-bottomright"><?php _e('操作'); ?></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php $page = $_GET['page'] ? $_GET['page'] : 1 ; ?>
                        <?php $links = $db->fetchAll($db->select()->from('table.golinks')->page($page, 20)->order('table.golinks.id', Typecho_Db::SORT_DESC)); ?>
                        <?php foreach($links as $link): ?>
                        <tr class="even" >
                            <td><input type="hidden" value="<?php _e($link['id']); ?>" name="cid[]"/></td>
                            <td>
                                <?php _e($link['key']); ?><br>
                                <span class="description" ><?php $options->index('/go/'.$link['key'].'/');?></span>
                            </td>
                            <td><?php _e($link['target']); ?></td>
                            <td><?php _e($link['count']); ?></td>
                            <td>                               
                                <a lang="<?php _e('你确认要删除该链接吗?'); ?>" href="<?php $options->index('/action/golinks?del=' . $link['id']); ?>" class="operate-delete"><?php _e('删除'); ?></a>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
                <div class="typecho-pager">
                    <div class="typecho-pager-content">
                        <ul>                            
                            <?php $total = $db->fetchObject($db->select(array('COUNT(id)' => 'num'))->from('table.golinks'))->num; ?>
                            <?php for($i=1;$i<=ceil($total/20);$i++): ?>
                            <li class='current'><a href="<?php $options->adminUrl('extending.php?panel=GoLinks%2Fpanel.php&page='.$i); ?>" style= 'cursor:pointer;' title='第 <?php _e($i); ?> 页'> <?php _e($i); ?> </a></li>
                            <?php endfor; ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>