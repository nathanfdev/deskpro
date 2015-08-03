/** Load all departments. */
export function loadPeople(options) {
    let req = [];
    if(options.is_me) {
        req.push('is_me=1');
    }
    if(options.is_agent) {
        req.push('is_agent=1');
    }
    const request = req.length > 0 ? ('?' + req.join('&')) : '';
    return DpApi.sendGet(`DP_API/people${request}`);
}

export function loadPerson(id) {
    return DpApi.sendGet(`DP_API/people/${id}`);
}
