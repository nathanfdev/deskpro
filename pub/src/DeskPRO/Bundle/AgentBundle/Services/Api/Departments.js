import DpApi from "../DpApi";

/** Load all departments. */
export function loadDepartments() {
    return DpApi.sendGet('DP_API/departments/');
}

export function loadDepartment(id) {
    return DpApi.sendGet(`DP_API/departments/${id}`);
}
