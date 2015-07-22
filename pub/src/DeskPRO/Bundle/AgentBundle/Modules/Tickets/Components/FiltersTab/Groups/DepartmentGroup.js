import React from "react";

import { connect } from "redux/react";
import * as DepartmentsActions from "../../../Actions/DepartmentsActions";

@connect(state => ({
  departments: state.departments,
}))
export default class DepartmentGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, departments } = this.props;
    this.department_id = this.props.item;

    if(this.department_id && typeof(departments[this.department_id]) === 'undefined') {
      dispatch(DepartmentsActions.loadDepartment(this.department_id));
    }
  }
  
  render() {
    const { departments, count, item } = this.props;
    
    if(!departments[this.department_id]) {
      return (<span></span>);
    } else {
      const key = "department-" + this.department_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{departments[this.department_id].title}</a>
        </li>
      );
    }
  }
}
