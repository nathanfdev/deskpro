import * as People from "./People";

/** Load all departments. */
export function loadAgents() {
    return People.loadPeople({is_agent: 1});
}

export function loadAgent(id) {
    return People.loadPerson(id);
}
