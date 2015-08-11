import React from "react";

import { connect } from "react-redux";
import * as DepartmentsActions from "../../../Actions/DepartmentsActions";

@connect(state => ({
  Departments: state.Departments,
}))
export default class DepartmentGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, Departments } = this.props;
    this.department_id = this.props.item;

    if(this.department_id && typeof(Departments[this.department_id]) === 'undefined') {
      dispatch(DepartmentsActions.loadDepartment(this.department_id));
    }
  }
  
  render() {
    const { Departments, count, item } = this.props;
    
    if(!Departments[this.department_id]) {
      return (<span></span>);
    } else {
      const key = "department-" + this.department_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{Departments[this.department_id].title}</a>
        </li>
      );
    }
  }
}
