import { Reducer } from "Ampliflux/reducers";

export default class TicketsList extends Reducer {
  getInitialState() {
    return {
    	TicketsList: [],
    };
  }

  registerHandlers() {this
    .r("TICKETS_LOAD_TICKETS", this.setPayload('TicketsList', 'data'))
  }
}
