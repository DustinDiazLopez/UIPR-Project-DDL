<?php
$title_tag = 'Admin Panel';
include_once('templates/header.php');

function ddlprint($arr, $name = 'debug')
{
    echo '<hr />' . htmlspecialchars($name) . ' START <hr />';
    var_dump($arr);
    echo '<hr />' . htmlspecialchars($name) . ' END <hr />';
}


$admins = SQL_GET_ADMINS();
$authors = query(SQL_GET_AUTHORS);
$subjects = query(SQL_GET_SUBJECTS);
$types = query(SQL_GET_DOC_TYPES);
$o_types = SQL_GET_ORPHANED_TYPES();
$files = SQL_GET_ORPHANED_FILES();

?>


<style>
    thead th {
        position: sticky;
        top: 0;
        background-color: white;
        margin: 0;
        padding: 0;
    }

    .dialog-window {
        height: 450px;
        width: auto;
        overflow: hidden;
    }

    .scrollable-content {
        height: 450px;
        overflow: auto;
    }
</style>

<header style="margin: 10px;">
    <h1>Admin Panel</h1>
    <p>In this panel, you will be able to edit, view or delete various data in the system</p>
</header>
<hr />

<div class="container-fluid">
    <?php

    if (isset($_GET['error']) && isset($_GET['msg'])) {
        echo showWarn(htmlspecialchars($_GET['error']), htmlspecialchars($_GET['msg']));
    } elseif (isset($_GET['success']) && isset($_GET['msg'])) {
        echo showSuccess(htmlspecialchars($_GET['success']), htmlspecialchars($_GET['msg']));
    } elseif (isset($_GET['fatalerror']) && isset($_GET['msg'])) {
        echo showDanger(htmlspecialchars($_GET['fatalerror']), htmlspecialchars($_GET['msg']));
    }
    ?>
</div>

<div class="container-fluid">
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-link active" id="admins-tab" data-toggle="tab" href="#admins" role="tab" aria-controls="admins" aria-selected="true"><i class="fas fa-user-shield"></i> Admins</a>
            <a class="nav-item nav-link" id="nav-data-tab" data-toggle="tab" href="#nav-data" role="tab" aria-controls="nav-data" aria-selected="false"><i class="fas fa-database"></i> Data</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">

        <div class="tab-pane fade" id="nav-data" role="tabpanel" aria-labelledby="nav-data-tab">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" id="authors-tab" data-toggle="tab" href="#authors" role="tab" aria-controls="authors" aria-selected="false"><i class="fas fa-users-cog"></i> Authors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="subjects-tab" data-toggle="tab" href="#subjects" role="tab" aria-controls="subjects" aria-selected="false"><i class="fas fa-sitemap"></i> Subjects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="types-tab" data-toggle="tab" href="#types" role="tab" aria-controls="types" aria-selected="false"><i class="fas fa-link"></i> Types</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="oprphaned-type-tab" data-toggle="tab" href="#oprphaned-type" role="tab" aria-controls="oprphaned-type" aria-selected="false"><i class="fas fa-unlink"></i> Orphaned Types</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false"><i class="fas fa-archive"></i> Orphaned Files</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="admins" role="tabpanel" aria-labelledby="admins-tab">
            <div class="row">
                <div class="col-sm-4 bg-light">
                    <?php include_once('templates/create.admin.view.php'); ?>
                </div>
                <div class="col-sm-8">
                    <div class="container-fluid dialog-window">
                        <div class="scrollable-content">

                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">E-mail</th>
                                        <th scope="col">Edit <?php hint('As you\'ll notice, you cannot edit the root user.'); ?></th>
                                        <th scope="col">Delete <?php hint('As you\'ll notice, you cannot delete the root user, nor your own account, you will have to delete your own account through another account.'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($admins) > 0) :
                                        function is_root($obj)
                                        {
                                            return ($obj['email'] === 'dustindiazlopez98@gmail.com' && $obj['username'] === 'root') === TRUE;
                                        }

                                        $is_root = FALSE;
                                        foreach ($admins as $obj) :
                                            $is_root = is_root($obj);
                                    ?>
                                            <tr>
                                                <th scope="row"><?php echo $obj['id']; ?></th>
                                                <td><?php echo $obj['username']; ?></td>
                                                <td><a href="mailto:<?php echo $obj['email']; ?>"><?php echo $obj['email']; ?></a></td>

                                                <?php
                                                if ($is_root) {
                                                ?>
                                                    <td>
                                                        <button class="btn btn-primary disabled" style="width:100%;height:100%;" disabled aria-disabled="true"><i class="fas fa-user-edit"></i><span class="sr-only">edit user <?php echo $obj['username']; ?></span></button>
                                                    </td>
                                                <?php
                                                } else {
                                                ?>
                                                    <td>
                                                        <button type="submit" class="btn btn-primary" style="width:100%;height:100%;" data-toggle="modal" data-target="#editAdmin<?php echo $obj['id']; ?>"><span class="sr-only">edit user <?php echo $obj['username'];?> </span><i class="fas fa-user-edit"></i></button>
                                                    </td>

                                                    <!-- Edit Modal START -->
                                                    <div class="modal fade" id="editAdmin<?php echo $obj['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm edit for <?php echo $obj['username']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalAdminEdit<?php echo $obj['id']; ?>">Edit the user <strong><?php echo $obj['username']; ?></strong></h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="edit.row.php" method="POST" style="padding:0;margin:0;">
                                                                    <div class="modal-body">
                                                                        <div class="form-group">
                                                                            <input type="hidden" id="user<?php echo $obj['id']; ?>" name="userid" aria-describedby="userid" value="<?php echo $obj['id']; ?>" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="username">New Username</label>
                                                                            <input type="text" class="form-control" id="username<?php echo $obj['id']; ?>" name="username" aria-describedby="newUsername" value="<?php echo $obj['username']; ?>" placeholder="<?php echo $obj['username']; ?>" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="email">New E-mail</label>
                                                                            <input type="email" class="form-control" id="email<?php echo $obj['id']; ?>" name="email" aria-describedby="newEmail" value="<?php echo $obj['email']; ?>" placeholder="<?php echo $obj['email']; ?>" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="newpwd">New Password</label>
                                                                            <input type="password" class="form-control" id="newpwd<?php echo $obj['id']; ?>" name="newpwd" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <input type="hidden" id="confirmAdminEdit<?php echo $obj['id']; ?>" name="admin-to-edit" value="<?php echo $obj['id']; ?>">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary" name="edit-admin">Edit <?php echo $obj['username']; ?></button>

                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Edit Modal END -->
                                                <?php } ?>

                                                <td>
                                                    <?php
                                                    if (($obj['id'] == $_SESSION['id'][0]['id']) || $is_root) {
                                                        if ($is_root) {
                                                    ?>
                                                            <button class="btn btn-danger disabled" style="width:100%;height:100%;" disabled aria-disabled="true"><span class="sr-only">delete user <?php echo $obj['username'];?> </span><i class="fas fa-user-alt-slash"></i></button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button class="btn btn-danger disabled" style="width:100%;height:100%;" disabled aria-disabled="true"><span class="sr-only">delete user <?php echo $obj['username'];?> </span><i class="fas fa-user-alt-slash"></i></button>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <button type="submit" class="btn btn-danger" style="width:100%;height:100%;" data-toggle="modal" data-target="#deleteAdmin<?php echo $obj['id']; ?>"><span class="sr-only">delete user <?php echo $obj['username'];?> </span><i class="fas fa-user-alt-slash"></i></button>

                                                        <!-- Delete Modal START -->
                                                        <div class="modal fade" id="deleteAdmin<?php echo $obj['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm deletion for <?php echo $obj['username']; ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="modalAdminDelete<?php echo $obj['id']; ?>">Delete the user <strong><?php echo $obj['username']; ?></strong></h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Do keep in mind that this action is <strong title="cannot be undone">irreversible</strong>, and the user will no longer have access to the system.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <form action="delete.row.php" method="POST" style="padding:0;margin:0;">
                                                                            <input type="hidden" id="confirmAdminDelete<?php echo $obj['id']; ?>" name="admin-to-delete" value="<?php echo $obj['id']; ?>">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                            <button type="submit" class="btn btn-danger" name="delete-admin">I understand, delete <?php echo $obj['username']; ?>.</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Delete Modal END -->
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="authors" role="tabpanel" aria-labelledby="authors-tab">
            <div class="container-fluid dialog-window">
                <div class="scrollable-content">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Author</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($authors) > 0) :
                                foreach ($authors as $obj) :
                            ?>
                                    <tr>
                                        <th scope="row"><?php echo $obj['id']; ?></th>
                                        <td><?php echo $obj['author_name']; ?></td>
                                        <td>
                                            <button type="submit" class="btn btn-primary" style="width:100%;height:100%;" data-toggle="modal" data-target="#editAuthor<?php echo $obj['id']; ?>"><span class="sr-only">edit author <?php echo $obj['author_name'];?> </span><i class="fas fa-pencil-alt"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal START -->
                                    <div class="modal fade" id="editAuthor<?php echo $obj['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm edit for <?php echo $obj['author_name']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalAuthor<?php echo $obj['id']; ?>">Edit the author <strong><?php echo $obj['author_name']; ?></strong></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="edit.row.php" method="POST" style="padding:0;margin:0;">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="edit-author-name">Author:</label>
                                                            <input type="text" class="form-control" id="edit-author-name<?php echo $obj['id']; ?>" name="edit-author-name" aria-describedby="edit author name" value="<?php echo $obj['author_name']; ?>" placeholder="<?php echo $obj['author_name']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" id="confirmAuthorEdit<?php echo $obj['id']; ?>" name="author-to-edit" value="<?php echo $obj['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary" name="edit-author">Edit <?php echo $obj['author_name']; ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Edit Modal END -->
                            <?php
                                endforeach;
                            endif;
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="subjects" role="tabpanel" aria-labelledby="subjects-tab">
            <div class="container-fluid dialog-window">
                <div class="scrollable-content">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($subjects) > 0) :
                                foreach ($subjects as $obj) :
                            ?>
                                    <tr>
                                        <th scope="row"><?php echo $obj['id']; ?></th>
                                        <td><?php echo $obj['subject']; ?></td>
                                        <td>
                                            <button type="submit" class="btn btn-primary" style="width:100%;height:100%;" data-toggle="modal" data-target="#editSubject<?php echo $obj['id']; ?>"><span class="sr-only">edit subject <?php echo $obj['subject'];?> </span><i class="fas fa-pencil-alt"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal START -->
                                    <div class="modal fade" id="editSubject<?php echo $obj['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm edit for <?php echo $obj['subject']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalSubject<?php echo $obj['id']; ?>">Edit the subject <strong><?php echo $obj['subject']; ?></strong></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="edit.row.php" method="POST" style="padding:0;margin:0;">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="edit-subject-name">Subject:</label>
                                                            <input type="text" class="form-control" id="edit-subject-name<?php echo $obj['id']; ?>" name="edit-subject-name" aria-describedby="edit subject name" value="<?php echo $obj['subject']; ?>" placeholder="<?php echo $obj['subject']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" id="confirmSubjectEdit<?php echo $obj['id']; ?>" name="subject-to-edit" value="<?php echo $obj['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary" name="edit-subject">Edit <?php echo $obj['subject']; ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Edit Modal END -->
                            <?php
                                endforeach;
                            endif;
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="types" role="tabpanel" aria-labelledby="types-tab">
            <div class="row">
                <div class="col-sm-4 bg-light">
                    <?php include_once('templates/create.type.view.php'); ?>
                </div>
                <div class="col-sm-8">
                    <div class="container-fluid dialog-window">
                        <div class="scrollable-content">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Edit</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (count($types) > 0) :
                                    foreach ($types as $type) :
                                        ?>
                                        <tr>
                                            <th scope="row"><?php echo $type['id']; ?></th>
                                            <td><?php echo $type['type']; ?></td>
                                            <td>
                                                <button type="submit" class="btn btn-primary" style="width:100%;height:100%;" data-toggle="modal" data-target="#editType<?php echo $type['id']; ?>"><span class="sr-only">edit type <?php echo $type['type'];?> </span><i class="fas fa-pencil-alt"></i></button>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal START -->
                                        <div class="modal fade" id="editType<?php echo $type['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm edit for <?php echo $type['type']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalType<?php echo $type['id']; ?>">Edit the type <strong><?php echo $type['type']; ?></strong></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="edit.row.php" method="POST" style="padding:0;margin:0;">
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="edit-type-name-for-<?php echo $type['id']; ?>">Type:</label>
                                                                <input type="text" class="form-control" id="edit-type-name-for-<?php echo $type['id']; ?>" name="edit-type-name" aria-describedby="edit type name" value="<?php echo $type['type']; ?>" placeholder="<?php echo $type['type']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" id="confirmTypeEdit<?php echo $type['id']; ?>" name="type-to-edit" value="<?php echo $type['id']; ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary" name="edit-type">Edit <?php echo $type['type']; ?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Edit Modal END -->
                                    <?php
                                    endforeach;
                                endif;
                                ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="oprphaned-type" role="tabpanel" aria-labelledby="oprphaned-type-tab">
            <div class="container-fluid dialog-window">
                <div class="scrollable-content">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Orphaned Type</th>
                                <th scope="col">Edit</th>
                                <?php
                                $num_of_o_types = count($o_types);
                                $disabled = $num_of_o_types === 0 ? 'disabled' : '';
                                ?>
                                <th scope="col"><button type="submit" class="btn btn-danger <?php echo $disabled; ?>" name="delete-file" style="width:100%;height:100%;" data-toggle="modal" data-target="#deleteAllOrphanedTypes" <?php echo $disabled; ?>>Delete All</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Delete ALL Orphaned TYPES Modal START -->
                            <div class="modal fade" id="deleteAllOrphanedTypes" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteAllOrphanedTypesTitle">Delete <strong>ALL</strong> orphaned types:</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Do keep in mind that this action is <strong title="cannot be undone">irreversible</strong>, but safe.
                                        </div>
                                        <div class="modal-footer">
                                            <form action="delete.row.php" method="POST" style="padding:0px;margin:0px;">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger" name="delete-all-orphaned-types">I understand, delete all orphaned types.</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal ALL Orphaned TYPES  END -->
                            <?php
                            if ($num_of_o_types > 0) :
                                foreach ($o_types as $type) :
                            ?>
                                    <tr>
                                        <th scope="row"><?php echo $type['id']; ?></th>
                                        <td><?php echo $type['type']; ?></td>
                                        <td>
                                            <button type="submit" class="btn btn-primary" style="width:100%;height:100%;" data-toggle="modal" data-target="#editOrphanedType<?php echo $type['id']; ?>Modal"><span class="sr-only">edit orpahned type <?php echo $type['type'];?> </span><i class="fas fa-pencil-alt"></i></button>
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-danger" style="width:100%;height:100%;" data-toggle="modal" data-target="#deleteOprhanedType<?php echo $type['id']; ?>Modal"><span class="sr-only">delete orpahned type <?php echo $type['type'];?> </span><i class="fas fa-eraser"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal START -->
                                    <div class="modal fade" id="editOrphanedType<?php echo $type['id']; ?>Modal" tabindex="-1" role="dialog" aria-labelledby="confirm edit for <?php echo $type['type']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalOprhanedType<?php echo $type['id']; ?>">Edit the orphaned type <strong><?php echo $type['type']; ?></strong></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="edit.row.php" method="POST" style="padding:0;margin:0;">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="edit-o-type-name-for-<?php echo $type['id']; ?>">Orphaned Type:</label>
                                                            <input type="text" class="form-control" id="edit-o-type-name-for-<?php echo $type['id']; ?>" name="new-o-type-name" aria-describedby="edit orphaned type name" value="<?php echo $type['type']; ?>" placeholder="<?php echo $type['type']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" id="confirmOprhanedTypeEdit<?php echo $type['id']; ?>" name="oprphaned-type-to-edit" value="<?php echo $type['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary" name="edit-oprphaned-type">Edit <?php echo $type['type']; ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Edit Modal END -->

                                    <!-- Delete Modal START -->
                                    <div class="modal fade" id="deleteOprhanedType<?php echo $type['id']; ?>Modal" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalOprhanedType<?php echo $type['id']; ?>">Delete the orphaned type <strong><?php echo $type['type']; ?></strong></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Do keep in mind that this action is <strong title="cannot be undone">irreversible</strong>.
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="delete.row.php" method="POST" style="padding:0px;margin:0px;">
                                                        <input type="hidden" id="confirmOprhanedTypeDelete<?php echo $type['id']; ?>" name="oprphaned-type-to-delete" value="<?php echo $type['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger" name="delete-oprphaned-type">Delete <?php echo $type['type']; ?></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Delete Modal END -->
                            <?php
                                endforeach;
                            endif;
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
            <div class="container-fluid dialog-window">
                <div class="scrollable-content">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope = "col">File Name </th>
                                <th scope = "col">File Type </th>
                                <th scope = "col">File Size </th>
                                <th scope = "col">Share </th>
                                <th scope = "col">Download </th>
                                <th scope = "col">New Tab </th>
                                <th scope = "col">Popup Window </th>
                                <?php
                                $num_of_files = count($files);
                                $disabled = $num_of_files === 0 ? 'disabled' : '';
                                ?>
                                <th scope="col"><button type="submit" class="btn btn-danger <?php echo $disabled; ?>" name="delete-file" style="width:100%;height:100%;" data-toggle="modal" data-target="#deleteAllOrphanedFiles" <?php echo $disabled; ?>>Delete All</button></th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Delete ALL FILES Modal START -->
                            <div class="modal fade" id="deleteAllOrphanedFiles" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteAllOrphanedFilesTitle">Delete <strong>ALL</strong> Orphaned Files:</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Do keep in mind that this action is <strong title="cannot be undone">irreversible</strong>, and the orphaned files will be lost <strong title="a long time!">forever</strong>.
                                        </div>
                                        <div class="modal-footer">
                                            <form action="delete.row.php" method="POST" style="padding:0px;margin:0px;">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger" name="delete-all-orphaned-files">I understand, delete all the orphaned files.</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal ALL FILES  END -->
                            <?php
                            if ($num_of_files > 0) :
                                foreach ($files as $f) :
                            ?>
                                    <!-- FILE ROW START -->
                                    <tr>
                                        <th scope="row"><?php echo $f['id']; ?></th>
                                        <td scope="row" class="file"><?php echo $f['filename']; ?></td>
                                        <td scope="row" class="font-weight-light"><?php echo mime_content_type(PATH_TO_FILES_FOLDER . $f['path']); ?></td>
                                        <td scope="row" class="font-weight-light"><?php echo filesize(PATH_TO_FILES_FOLDER . $f['path']) / 1e+6; ?> MB</td>
                                        <td scope="row">
                                            <input type="text" style="display: none" value="<?php

                                            $encoded_id = urlencode(base64_encode('head-' . $f['id']));
                                            echo shareURL($f['id']);

                                            ?>" id="share-admin-<?php echo $f['id']; ?>">
                                            <button type="submit" class="btn btn-light" id="share-admin-btn-<?php echo $f['id']; ?>"
                                                    style="width:100%;height:100%;" onclick="copyValueToClipboard('share-admin-<?php echo $f['id']; ?>', 'share-admin-btn-<?php echo $f['id']; ?>', true)">
                                                <i class="fas fa-share-alt" onclick="copyValueToClipboard('share-admin-<?php echo $f['id']; ?>', 'share-admin-btn-<?php echo $f['id']; ?>', true)"></i> <span class="sr-only">Share <?php echo $f['filename']; ?>.</span>
                                            </button>
                                        </td>
                                        <td scope="row">
                                            <form action="fetch.file.php" method="GET" style="padding:0px;margin:0px;" target="_blank">
                                                <input type="hidden" id="<?php echo $f['id']; ?>Download" name="file" value="<?php echo $encoded_id; ?>">
                                                <button type="submit" class="btn btn-light"  name="download" style="width:100%;height:100%;"><i class="fas fa-download"></i> <span class="sr-only">Download <?php echo $f['filename']; ?></span></button>
                                            </form>
                                        </td>
                                        <td scope="row">
                                            <form action="file.php" method="GET" style="padding:0px;margin:0px;" target="_blank">
                                                <input type="hidden" id="<?php echo $f['id']; ?>ViewTab" name="file" value="<?php echo $encoded_id; ?>">
                                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;"><i class="fas fa-external-link-alt"></i></button>
                                            </form>
                                        </td>
                                        <td scope="row">
                                            <form action="file.php" method="GET" style="padding:0px;margin:0px;" onsubmit='window.open("", "open-pdf-view-", "width=800,height=600,resizable=yes")' target="open-pdf-view-">
                                                <input type="hidden" id="<?php echo $f['id']; ?>ViewPopup" name="file" value="<?php echo $encoded_id; ?>">
                                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;"><i class="far fa-window-restore"></i></button>
                                            </form>
                                        </td>
                                        <td scope="row">
                                            <button type="submit" class="btn btn-danger" name="delete-file" style="width:100%;height:100%;" data-toggle="modal" data-target="#deleteFile<?php echo $f['id']; ?>"><span class="sr-only">edit orpahned type <?php echo $type['type'];?> </span><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    <!-- FILE ROW END -->

                                    <!-- Delete Modal START -->
                                    <div class="modal fade" id="deleteFile<?php echo $f['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalLabel<?php echo $f['id']; ?>">Delete <strong><?php echo $f['filename']; ?> (ID: <?php echo $f['id']; ?>)</strong></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Do keep in mind that this action is <strong title="cannot be undone">irreversible</strong>, and the orphaned file will be lost <strong title="a long time!">forever</strong>.
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="delete.row.php" method="POST" style="padding:0px;margin:0px;">
                                                        <input type="hidden" id="confirmFileDelete<?php echo $f['id']; ?>" name="file-to-delete" value="<?php echo $f['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger" name="delete-file">I understand, delete the orphaned file.</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal END -->

                            <?php
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once('templates/footer.php');
