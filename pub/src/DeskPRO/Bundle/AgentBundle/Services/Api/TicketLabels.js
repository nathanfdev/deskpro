import DpApi from "../DpApi";

export function loadLabels() {
    return DpApi.sendGet('DP_API/ticket_labels');
}

export function loadLabelTickets(label_name) {
    return DpApi.sendGet('DP_API/ticket_labels/' + label_name + '/tickets');
}
