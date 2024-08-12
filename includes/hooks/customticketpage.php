<?php

add_hook('ClientAreaPageViewTicket', 1, function($vars) {

    if (isset($_GET['updatedetails']) && $_GET['updatedetails'] == '1') {

        // Verificar si se envió el formulario para limpiar los datos
        if (isset($_POST['clearcustomfields']) && $_POST['clearcustomfields'] == '1') {

            // Obtener el ticket ID a partir del tid
            $command = 'GetTicket';
            $postData = array(
                'ticketnum' => $vars['tid'], // Usar el tid para buscar el ticket
            );

            // Ejecutar la API para obtener el ticket ID
            $ticketDetails = localAPI($command, $postData);

            if ($ticketDetails['result'] == 'success') {
                $ticketId = $ticketDetails['ticketid']; // Obtener el ID del ticket

                // Preparar los campos personalizados para borrado (vaciar los valores)
                $customFieldsToClear = [];
                foreach ($ticketDetails['customfields'] as $customField) {
                    $customFieldsToClear[$customField['id']] = ''; // Borrar el valor del campo
                }

                // Ejecutar la API para actualizar el ticket con los campos vacíos
                $updateCommand = 'UpdateTicket';
                $updatePostData = array(
                    'ticketid' => $ticketId,
                    'customfields' => base64_encode(serialize($customFieldsToClear)),
                );

                $updateResults = localAPI($updateCommand, $updatePostData);

                // Redirigir después de la limpieza
                if ($updateResults['result'] == 'success') {
                    logActivity("Los campos personalizados han sido limpiados para el ticket ID: " . $ticketId);
                    header("Location: " . $vars['systemurl'] . "viewticket.php?tid=" . $vars['tid'] . "&c=" . $vars['c'] . "&cleared=1");
                    exit;
                } else {
                    echo '<pre>Error al limpiar los campos personalizados: ';
                    print_r($updateResults);
                    echo '</pre>';
                    exit;
                }
            } else {
                echo '<pre>Error al obtener los detalles del ticket: ';
                print_r($ticketDetails);
                echo '</pre>';
                exit;
            }
        }

        // Verificar si se envió el formulario para actualizar los datos
        if (isset($_POST['updatecustomfields']) && $_POST['updatecustomfields'] == '1') {

            // Obtener el ticket ID a partir del tid
            $command = 'GetTicket';
            $postData = array(
                'ticketnum' => $vars['tid'], // Usar el tid para buscar el ticket
            );

            // Ejecutar la API para obtener el ticket ID
            $ticketDetails = localAPI($command, $postData);

            if ($ticketDetails['result'] == 'success') {
                $ticketId = $ticketDetails['ticketid']; // Obtener el ID del ticket

                // Actualizar los campos personalizados
                $customFieldsToUpdate = [];
                foreach ($_POST['customfield'] as $id => $value) {
                    $customFieldsToUpdate[$id] = $value;
                }

                $updateCommand = 'UpdateTicket';
                $updatePostData = array(
                    'ticketid' => $ticketId, // Utilizar el ID del ticket obtenido
                    'customfields' => base64_encode(serialize($customFieldsToUpdate)),
                );

                $updateResults = localAPI($updateCommand, $updatePostData);

                // Redirigir después de la actualización
                if ($updateResults['result'] == 'success') {
                    header("Location: " . $vars['systemurl'] . "viewticket.php?tid=" . $vars['tid'] . "&c=" . $vars['c']);
                    exit;
                } else {
                    echo '<pre>Error al actualizar los campos personalizados: ';
                    print_r($updateResults);
                    echo '</pre>';
                    exit;
                }
            } else {
                echo '<pre>Error al obtener los detalles del ticket: ';
                print_r($ticketDetails);
                echo '</pre>';
                exit;
            }
        }

        // Mostrar la plantilla de actualización
        return [
            'showCustomContent' => true,
            'customFormAction' => $vars['systemurl'] . "viewticket.php?tid=" . $vars['tid'] . "&c=" . $vars['c'] . "&updatedetails=1",
            'csrfToken' => $vars['token'],
            'customfields' => $vars['customfields'], // Asegúrate de que los campos personalizados estén disponibles en la plantilla
            'tid' => $vars['tid'],
            'c' => $vars['c'],
        ];
    }

    return [];
});


// Hook para borrar los campos personalizados cuando se cierra el ticket
add_hook('TicketClose', 1, function($vars) {

    // Obtener el ticket ID a partir del tid
    $command = 'GetTicket';
    $postData = array(
        'ticketid' => $vars['ticketid'], // Usar el ID del ticket que se está cerrando
    );

    $ticketDetails = localAPI($command, $postData);

    if ($ticketDetails['result'] == 'success') {

        // Preparar los campos personalizados para borrado (vaciar los valores)
        $customFieldsToClear = [];
        if (!empty($ticketDetails['customfields'])) {
            foreach ($ticketDetails['customfields'] as $customField) {
                $customFieldsToClear[$customField['id']] = ''; // Borrar el valor del campo
            }

            // Actualizar los campos personalizados para borrarlos
            $updateCommand = 'UpdateTicket';
            $updatePostData = array(
                'ticketid' => $vars['ticketid'],
                'customfields' => base64_encode(serialize($customFieldsToClear)),
            );

            $updateResults = localAPI($updateCommand, $updatePostData);

            // Verificar si la operación fue exitosa
            if ($updateResults['result'] == 'success') {
                logActivity("Successfully cleared custom fields for ticket ID " . $vars['ticketid']);
            } else {
                logActivity("Error clearing custom fields for ticket ID " . $vars['ticketid'] . ": " . $updateResults['message']);
            }
        } else {
            logActivity("No custom fields found for ticket ID " . $vars['ticketid']);
        }
    } else {
        logActivity("Error retrieving ticket details for ticket ID " . $vars['ticketid'] . ": " . $ticketDetails['message']);
    }
});
