<?php

// read system configuration
include("config.php");
include("include/CountryModel.php");

// set page properties
$pageGroup = "backend";

// check session
include("include/session.checker.php");
$user_id = $_SESSION["user_id"]; 
$username = $_SESSION["username"];
$group = $_SESSION["group"];

// call helper functions
include("include/helper.functions.php");

// instantiate db
include("include/db.start.php");

// call user functions
include("include/users.php");
include("include/roles.php");
include("include/blocks.php");
include("include/portals.php");
include("include/journals.php");
include("include/date.php");
include("include/articles.php");
include('include/authors.php');
include('include/categories.php');
include("include/editors.php");
include("include/editorial-group.php");
include("include/reviewers.php");
include("include/FCKeditor/fckeditor.php");
include("include/ipt.php");
include("include/SqlPaginator.php");
include("include/CharPaginator.php");
include_once 'include/SimilarAuthorMapModel.php';

// call portal components
// include('portal.php');

$author_id = isset($_GET['id']) ? $_GET['id'] : null;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$limit = 20;

// Pagination
$offset = ($page - 1) * $limit;

$author_name = get_author_name($author_id);
$article_id = get_article_id_by_author($author_id);
$article = get_article_common_detail($article_id);
$article_title = $article['article_title'];
$journal_name = $article['journal_name'];
$affiliation_row = getAffiliation($author_id);
$affiliation = $affiliation_row['ipt'];
$group_id = SimilarAuthorMapModel::getAuthorUniqueId($author_id);

if (is_null($search)) {
  $total_records = SimilarAuthorMapModel::getUniqueAuthorIdCount() - 1;
  $uniq_author_ids = SimilarAuthorMapModel::getUniqueAuthorIds($limit, $offset);
} else {
  $total_records = SimilarAuthorMapModel::getUniqueAuthorIdCountByAuthorName($search) - 1;
  $uniq_author_ids = SimilarAuthorMapModel::findByAuthorName($search, $limit, $offset);
}


// Pagination
$paginator = new SqlPaginator($page, $limit, $total_records);
$paginator->setGetParameters(array('id' => $author_id));
$pagination = $paginator->getPagination();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>e-publication universiti malaya</title>
<script type="text/javascript" language="javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.colorbox-min.js"></script>
<script type="text/javascript" language="javascript" src="js/author_group_picker.js"></script>
<link href="css/admin_main.css" rel="stylesheet" type="text/css" media="screen" />
<link href="css/author_map_management.css" rel="stylesheet" type="text/css" media="screen" />
<link href="lib/style.css" rel="stylesheet" type="text/css" media="screen" />
<link href="css/colorbox.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0">
<?php include("blocks/main-menu-be.php"); ?>
<?php include "header.php"; ?>
<table width="979" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <table width="980" border="0" cellspacing="1" cellpadding="1">
        <tr>
          <td width="219" rowspan="3" valign="top"><?php include("blocks/menu.php"); ?> </td>
          <td width="750" height="25" colspan="2">
            <table width="100%"  border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td><div align="left" class="breadcrumb"><a href="admin.php">Home</a> &gt;    Article Management </div></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td valign="top">
            <table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td height="30" background="images/tajukpanjang750.png"><span class="fontWhiteBold">Author Grouping</span>
              </tr>
              <tr>
                <td>
                  <table width="720" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <div align="left"><span class="title"></span></div>
                        <div class="adminContent authorMap">
                          <div class="info">
                            <h3><?php echo $author_name ?> <?php if ($affiliation) echo "($affiliation)" ?></h3>
                            <p>The author of <em><?php echo $article_title ?></em>, <?php echo $journal_name ?></p>
                            <?php if ($group_id): ?>
                              <p>Currently belong to author group <a href="#"><?php echo $group_id ?> (View detail)</a> | <a href="#">Remove from this group</a></p>
                            <?php else: ?>
                              <p>This author is not associated with any author group</p>
                            <?php endif; ?>
                            <p>Move this author into any of the following groups:</p>
                          </div>
                          <div class="searchBar">
                            <hr />  
                            <form action="author_map_management_author_edit.php" method="get">
                              <a href="author_map_management_author_edit.php?id=<?php echo $author_id ?>">All</a>
                              <span>|</span>
                              <label for="search">Author group search:</label>
                              <input type="text" name="search" value="<?php echo $search ?>" />
                              <input type="hidden" name="id" value="<?php echo $author_id ?>" />
                              <input type="submit" name="submit" value="Search" />
                            </form>
                            <hr />  
                          </div>
                          <div class="resultList">
                            <?php if (!$uniq_author_ids): ?>
                              <p>No result</p>
                            <?php else: ?>
                              <div class="pagination"><?php echo $pagination ?></div>
                              <table class="resultList">
                                <?php $count = $offset; ?>
                                <tr>
                                  <th class="groupId">Group ID</th>
                                  <th>Author names</th>
                                  <th class="affiliation">Affiliation</th>
                                  <th class="detail"></th>
                                  <th class="action"></th>
                                </tr>  
                                <?php while ($row_uniq_author_id = mysql_fetch_array($uniq_author_ids)): ?>
                                  <?php
                                  $row_uniq_id = $row_uniq_author_id['unique_id'];
                                  $authors = SimilarAuthorMapModel::getAuthorsByUniqueIdGroupByName($row_uniq_id, 4);
                                  $affils = SimilarAuthorMapModel::getAffiliationsByUniqueId($row_uniq_id, 5);
                                  ?>
                                  <tr class="<?php echo ++$count % 2 == 0 ? 'even' : 'odd' ?>">
                                    <td class="rightPadded">
                                      <a title="Edit group <?php $row_uniq_id ?>" href="author_map_management_group.php?id=<?php echo $row_uniq_id ?>"><?php echo $row_uniq_id ?></a>
                                    </td>
                                    <td>
                                      <ul class='separatedItem'>
                                        <?php $tempCount = 0 ?>
                                        <?php while ($author = mysql_fetch_array($authors)): ?>
                                          <li><?php echo $author['author_name'] ?></li>
                                        <?php endwhile; ?>
                                      </ul>
                                    </td>
                                    <td>
                                      <ul class='separatedItem'>
                                      <?php $tempCount = 0 ?>
                                      <?php while ($affil = mysql_fetch_array($affils)): ?>
                                        <?php if (++$tempCount == 5) break ?>
                                        <li><?php echo $affil['affil_name'] ?></li>
                                      <?php endwhile; ?>
                                      <?php if ($tempCount == 5): ?><li class="more">More ...</li><?php endif; ?>
                                      </ul>
                                    </td>
                                    <td><a title="See the details of the authors associated with group <?php echo $uniq_id ?>" href="#" onclick="getGroupDetails(<?php echo $row_uniq_id ?>); return false">Details</a></td>
                                    <td><input onclick="mergeAuthorIntoGroup(<?php echo $unique_id ?>, <?php echo $row_uniq_id ?>)" type="button" value="Merge" title="Merge group <?php echo $unique_id ?> into group <?php echo $row_uniq_id ?>" /></td>
                                  </tr>
                                <?php endwhile; ?>
                              </table>
                              <div class="pagination"><?php echo $pagination ?></div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
          <td width="1">
          </td>
        </tr>
      </table>
<!-- start of wrapper -->
    </td>
  </tr>
</table>
<?php include "footer.php"; ?>
<!-- end of wrapper -->
	<div class="loadingDialog" style="display: none">
    <h1>Loading...</h1>
  </div>
  <div id="groupDetailDialog" style="display: none">
    <div class="close"><a href="#" class="close">Close [X]</a></div>
    <div class="groupDetailDialogInner">
      <div class="pagination dialogPagination"></div>
      <div class="clear"></div>
      <table class="authorList">
        <thead>
          <tr>
            <th width="80px">Author</th>
            <th>Article</th>
            <th width="120px">Journal</th>
            <th width="120px">Affiliation</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <div class="pagination dialogPagination"></div>
      <div class="clear"></div>
    </div>
  </div>
</body>
</html>
