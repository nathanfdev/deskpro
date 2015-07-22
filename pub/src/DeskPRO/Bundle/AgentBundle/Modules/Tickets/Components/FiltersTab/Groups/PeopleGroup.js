import React from "react";

import { connect } from "redux/react";
import * as PeopleActions from "../../../Actions/PeopleActions";

@connect(state => ({
  people: state.people,
}))
export default class PeopleGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, people } = this.props;
    this.agent_id = this.props.item;

    if(this.agent_id && typeof(people[this.agent_id]) === 'undefined') {
      dispatch(PeopleActions.loadPeople(this.agent_id));
    }
  }
  
  render() {
    const { people, count, item } = this.props;
    
    if(!people[this.agent_id]) {
      return (<span></span>);
    } else {
      const key = "agent-" + this.agent_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{people[this.agent_id].name}</a>
        </li>
      );
    }
  }
}
