import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export function loadLabels() {
    return api.sendGet('DP_API/ticket_labels');
}

export function loadLabelTickets(label_name) {
    return api.sendGet('DP_API/ticket_labels/' + label_name + '/tickets');
}
