import React from "react";

import { connect } from "redux/react";
import * as DepartmentsActions from "../../Actions/DepartmentsActions";

export default class TicketsTabFilterGroup extends React.Component {
  render() {
    const {grouping, count, item} = this.props;
    
    return (
      <li>
        <div className="list-counter-bucket"><a href="#" className="list-counter">{group.count}</a></div>
        <a href="#" className="item">{group[filter_groups.grouping]}</a>
      </li>
    );
  }
}

@connect(state => ({
  departments: state.departments,
}))
export class TicketsTabFilterDepartmentGroup extends TicketsTabFilterGroup {
  constructor(props) {
    super(props);
    const { dispatch, departments } = this.props;
    this.department_id = this.props.item;
    
    if(this.department_id && typeof(departments[this.department_id]) === 'undefined') {
      dispatch(DepartmentsActions.loadDepartment(this.department_id));
    }
  }
  
  render() {
    const { departments } = this.props;
    console.log(departments);
    
    if(!departments[this.department_id]) {
      return (<span></span>);
    } else {
      const key = "department-" + this.department_id;
      return (
        <li key={key}>
          <a href="#" className="item">{departments[this.department_id].title}</a>
        </li>
      );
    }
  }
}
