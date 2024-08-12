<?php

// Hook para manejar la visualización y actualización de los campos personalizados
add_hook('ClientAreaPageViewTicket', 1, function($vars) {

    if (isset($_GET['updatedetails']) && $_GET['updatedetails'] == '1') {

        // Verificar si se envió el formulario
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
                header("Location: " . $vars['systemurl'] . "viewticket.php?tid=" . $vars['tid'] . "&c=" . $vars['c']);
                exit;
            } else {
                // Manejo de error al obtener el ticket ID
                echo '<pre>';
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
