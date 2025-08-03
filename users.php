<?php 
require_once 'php_action/db_connect.php'; 

// ID del usuario 'admin' que no puede ser modificado
$admin_user_id_to_protect = 1;

// Handle form submissions for user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    // Condición de seguridad: Bloquea cualquier acción sobre el usuario 'admin' en el lado del servidor
    if ($id && $id == $admin_user_id_to_protect) {
        header("Location: users.php?error=no_permission");
        exit();
    }

    if (isset($_POST['add_user'])) {
        $name = $_POST['username'];
        $password = md5($_POST['password']);
        $stmt = $connect->prepare("INSERT INTO users (username, password, active, role) VALUES (?, ?, 1, 3)"); // El rol por defecto es 3 (Visualizador)
        $stmt->bind_param("ss", $name, $password);
        if ($stmt->execute()) {
             header("Location: users.php?success=add_user");
        } else {
             header("Location: users.php?error=add_user_failed");
        }
        exit();
    } elseif (isset($_POST['update_user'])) {
        $name = $_POST['name'];
        $stmt = $connect->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            header("Location: users.php?success=update_user");
        } else {
            header("Location: users.php?error=update_user_failed");
        }
        exit();
    } elseif (isset($_POST['update_password'])) {
        $password = md5($_POST['password']);
        $stmt = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $password, $id);
        if ($stmt->execute()) {
            header("Location: users.php?success=update_password");
        } else {
            header("Location: users.php?error=update_password_failed");
        }
        exit();
    } elseif (isset($_POST['toggle_active'])) {
        $active = $_POST['active'];
        $new_status = $active ? 0 : 1;
        $stmt = $connect->prepare("UPDATE users SET active = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        if ($stmt->execute()) {
            header("Location: users.php?success=toggle_active");
        } else {
            header("Location: users.php?error=toggle_active_failed");
        }
        exit();
    } elseif (isset($_POST['delete_user'])) {
        $stmt = $connect->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: users.php?success=delete_user");
        } else {
            header("Location: users.php?error=delete_user_failed");
        }
        exit();
    } elseif (isset($_POST['update_role'])) {
        $role = $_POST['role'];
        $stmt = $connect->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $role, $id);
        if ($stmt->execute()) {
            header("Location: users.php?success=update_role");
        } else {
            header("Location: users.php?error=update_role_failed");
        }
        exit();
    }
    header("Location: users.php");
    exit();
}

require_once 'includes/header.php'; 

// Fetch all users with their roles
$result = $connect->query("SELECT user_id, username, active, role FROM users ORDER BY user_id");

?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li><a href="dashboard.php">Inicio</a></li>
            <li class="active">Usuarios</li>
        </ol>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php
                if ($_GET['success'] == 'add_user') echo 'El usuario ha sido agregado con éxito.';
                if ($_GET['success'] == 'update_user') echo 'El nombre de usuario ha sido actualizado.';
                if ($_GET['success'] == 'update_password') echo 'La contraseña ha sido actualizada.';
                if ($_GET['success'] == 'toggle_active') echo 'El estado del usuario ha sido cambiado.';
                if ($_GET['success'] == 'delete_user') echo 'El usuario ha sido eliminado.';
                if ($_GET['success'] == 'update_role') echo 'El rol del usuario ha sido cambiado.';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php
                if ($_GET['error'] == 'no_permission') echo 'No se puede editar al usuario administrador principal.';
                if ($_GET['error'] == 'add_user_failed') echo 'Error al agregar el usuario.';
                if ($_GET['error'] == 'update_user_failed') echo 'Error al actualizar el nombre.';
                if ($_GET['error'] == 'update_password_failed') echo 'Error al actualizar la contraseña.';
                if ($_GET['error'] == 'toggle_active_failed') echo 'Error al cambiar el estado del usuario.';
                if ($_GET['error'] == 'delete_user_failed') echo 'Error al eliminar el usuario.';
                if ($_GET['error'] == 'update_role_failed') echo 'Error al cambiar el rol del usuario.';
                ?>
            </div>
        <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-user"></i> Gestionar Usuarios</div>
            </div> <div class="panel-body">

                <div class="remove-messages"></div>

                <div class="div-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal"> <i class="glyphicon glyphicon-plus-sign"></i> Agregar Usuario </button>
                </div> <table class="table table-hover table-striped" id="manageUserTable">
                    <thead>
                        <tr>
                            <th style="width:20%;">Nombre de Usuario</th>
                            <th style="width:10%;">Estado</th>
                            <th style="width:15%;">Rol</th>
                            <th style="width:55%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><span class="label <?php echo $row['active'] ? 'label-success' : 'label-danger'; ?>"><?php echo $row['active'] ? 'Activo' : 'Inactivo'; ?></span></td>
                                    <td>
                                        <?php
                                        if ($row['role'] == 1) {
                                            echo 'Super Admin';
                                        } elseif ($row['role'] == 2) {
                                            echo 'Admin';
                                        } elseif ($row['role'] == 3) {
                                            echo 'Visualizador';
                                        } else {
                                            echo 'Desconocido';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['user_id'] == $admin_user_id_to_protect): ?>
                                            <span class="label label-warning">No se puede editar</span>
                                        <?php else: ?>
                                            <form action="users.php" method="post" class="form-inline" style="display:inline-block; margin-right: 5px;">
                                                <input type="hidden" name="update_user">
                                                <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                                <div class="form-group">
                                                    <input type="text" name="name" class="form-control" placeholder="Nuevo nombre" required>
                                                </div>
                                                <button type="submit" class="btn btn-info btn-sm" title="Cambiar nombre" onclick="return confirm('¿Estás seguro de que quieres cambiar el nombre de usuario?');"><i class="glyphicon glyphicon-pencil"></i></button>
                                            </form>

                                            <form action="users.php" method="post" class="form-inline" style="display:inline-block; margin-right: 5px;">
                                                <input type="hidden" name="update_password">
                                                <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                                <div class="form-group">
                                                    <input type="password" name="password" class="form-control" placeholder="Nueva clave" required>
                                                </div>
                                                <button type="submit" class="btn btn-warning btn-sm" title="Cambiar clave" onclick="return confirm('¿Estás seguro de que quieres cambiar la contraseña?');"><i class="glyphicon glyphicon-lock"></i></button>
                                            </form>

                                            <form action="users.php" method="post" style="display:inline-block; margin-right: 5px;">
                                                <input type="hidden" name="toggle_active">
                                                <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                                <input type="hidden" name="active" value="<?php echo $row['active']; ?>">
                                                <button type="submit" class="btn <?php echo $row['active'] ? 'btn-danger' : 'btn-success'; ?> btn-sm" title="<?php echo $row['active'] ? 'Desactivar' : 'Activar'; ?>" onclick="return confirm('¿Estás seguro de que quieres <?php echo $row['active'] ? 'desactivar' : 'activar'; ?> a este usuario?');"><i class="glyphicon <?php echo $row['active'] ? 'glyphicon-remove' : 'glyphicon-ok'; ?>"></i></button>
                                            </form>

                                            <form action="users.php" method="post" class="form-inline" style="display:inline-block; margin-right: 5px;">
                                                <input type="hidden" name="update_role">
                                                <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                                <div class="form-group">
                                                    <select name="role" class="form-control" required>
                                                        <option value="1" <?php echo ($row['role'] == 1) ? 'selected' : ''; ?>>Super Admin</option>
                                                        <option value="2" <?php echo ($row['role'] == 2) ? 'selected' : ''; ?>>Admin</option>
                                                        <option value="3" <?php echo ($row['role'] == 3) ? 'selected' : ''; ?>>Visualizador</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" title="Cambiar rol" onclick="return confirm('¿Estás seguro de que quieres cambiar el rol de este usuario?');"><i class="glyphicon glyphicon-transfer"></i></button>
                                            </form>

                                            <form action="users.php" method="post" style="display:inline-block;">
                                                <input type="hidden" name="delete_user">
                                                <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres borrar a este usuario?');" title="Eliminar"><i class="glyphicon glyphicon-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div> </div> </div> </div> <div class="modal fade" tabindex="-1" role="dialog" id="addUserModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-plus-sign"></i> Agregar Usuario</h4>
            </div>
            <form class="form-horizontal" id="submitUserForm" action="users.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="username" class="col-sm-3 control-label">Nombre de Usuario: </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de Usuario">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-sm-3 control-label">Contraseña: </label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="add_user" class="btn btn-primary" onclick="return confirm('¿Estás seguro de que quieres agregar este usuario?');">Guardar cambios</button>
                </div>
            </form>
        </div></div></div><?php require_once 'includes/footer.php'; ?>