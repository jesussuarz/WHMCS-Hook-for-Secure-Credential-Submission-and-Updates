<?php

// Cargar el sistema de WHMCS
require_once __DIR__ . '/init.php';
// Cargar Capsule
use WHMCS\Database\Capsule;

// IDs de los campos personalizados que deseas limpiar
$customFieldIDs = [3, 4, 5, 6, 7, 8, 9, 10, 11];

// Iniciar el script
echo "Script iniciado.\n";

// Buscar tickets cerrados
$closedTickets = Capsule::table('tbltickets')->where('status', 'Closed')->get();
echo "Tickets cerrados encontrados: " . count($closedTickets) . "\n";

// Verificar si hay tickets cerrados
if ($closedTickets->isEmpty()) {
    echo "No se encontraron tickets cerrados.\n";
    exit;
}

foreach ($closedTickets as $ticket) {
    echo "Procesando ticket con TID: {$ticket->tid} y C: {$ticket->c}\n";
    
    // Obtener el ticket ID a partir del tid y c
    $command = 'GetTicket';
    $postData = array(
        'ticketnum' => $ticket->tid,
        'c' => $ticket->c,
    );

    // Ejecutar la API para obtener los detalles del ticket
    $ticketDetails = localAPI($command, $postData);

    if ($ticketDetails['result'] == 'success') {
        $ticketId = $ticketDetails['ticketid'];
        echo "Ticket encontrado, ID: {$ticketId}, limpiando campos personalizados...\n";
        
        // Obtener los campos personalizados del ticket y filtrar solo los deseados
        $customFields = Capsule::table('tblcustomfieldsvalues')
            ->where('relid', $ticketId)
            ->whereIn('fieldid', $customFieldIDs)
            ->get();

        echo "Campos personalizados encontrados: " . print_r($customFields, true) . "\n";

        if ($customFields->isEmpty()) {
            echo "No se encontraron campos personalizados para el ticket ID: {$ticketId}\n";
            continue;
        }

        // Preparar los campos personalizados para ser limpiados
        $customFieldsToUpdate = [];
        foreach ($customFields as $field) {
            $customFieldsToUpdate[$field->fieldid] = ''; // Borrar el valor del campo
        }

        echo "Campos personalizados a limpiar: " . print_r($customFieldsToUpdate, true) . "\n";

        if (!empty($customFieldsToUpdate)) {
            $updateCommand = 'UpdateTicket';
            $updatePostData = array(
                'ticketid' => $ticketId,
                'customfields' => base64_encode(serialize($customFieldsToUpdate)),
            );

            echo "Datos enviados en la solicitud de actualización: " . print_r($updatePostData, true) . "\n";

            // Ejecutar la API para limpiar los campos personalizados
            $updateResults = localAPI($updateCommand, $updatePostData);

            echo "Resultado de la API UpdateTicket: " . print_r($updateResults, true) . "\n";

            if ($updateResults['result'] == 'success') {
                echo "Campos personalizados limpiados exitosamente para el ticket ID: {$ticketId}\n";
            } else {
                echo "Error al limpiar campos personalizados para el ticket ID: {$ticketId}: " . $updateResults['message'] . "\n";
            }
        } else {
            echo "No hay campos personalizados para limpiar en el ticket ID: {$ticketId}\n";
        }
    } else {
        echo "Error al obtener el ticket ID para el TID: {$ticket->tid}\n";
    }
}

echo "Script finalizado.\n";
?>


