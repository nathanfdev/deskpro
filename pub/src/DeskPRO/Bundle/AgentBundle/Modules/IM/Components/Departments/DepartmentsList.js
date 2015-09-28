import React from 'react';
import DepartmentsListItem from './DepartmentsListItem';

export default class DeparmentsList extends React.Component {
    render() {
        return (
            <ul className="im-list short">
                {this.props.departments.length > 0 ? this.props.departments.map((department, index) => <DepartmentsListItem key={index} department={department} />) : null}
            </ul>
        );
    }
}