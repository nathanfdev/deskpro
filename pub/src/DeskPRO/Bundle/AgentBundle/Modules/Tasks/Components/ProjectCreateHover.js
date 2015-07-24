import React from "react";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";

import * as TaskActions from "../Actions/TaskListActions";

export default class ProjectCreateHover extends React.Component {

    constructor(props) {
        super(props);
        this.enableButton = this.enableButton.bind(this);
        this.disableButton = this.disableButton.bind(this);
        this.state = {
            canSubmit: false,
            projectTitle: null
        };
    }

    enableButton() {
        this.setState({
            canSubmit: true
        });
    }

    disableButton() {
        this.setState({
            canSubmit: false
        })
    }

    updateValues(event) {
        this.setState({
            projectTitle: event.target.value
        })
    }

    serverValidation(field) {
        if (this.props.createdProject.failedProject === null
                || typeof this.props.createdProject.failedProject.errors === 'undefined'
                || this.props.createdProject.failedProject.errors === null
                || typeof this.props.createdProject.failedProject.errors.fields[field] === 'undefined') {
            return '';
        }

        let errors = this.props.createdProject.failedProject.errors.fields[field].errors;

        return errors.map(function(error) {
            return <span className="form-error-description" key={error.code}>{error.message}</span>
        });
    }

    parseMembers(members) {
        let result = {
            department: [],
            team: [],
            person: []
        };

        members.forEach(function(object) {
            let linkType = null;
            switch(true) {
                case object.department !== null:
                    linkType = 'department';
                    break;
                case object.team !== null:
                    linkType = 'team';
                    break;
                default:
                    linkType = 'person';
            }

            result[linkType].push(object[linkType].id);
        });

        return result;
    }

    render() {
        const {agentList, teamList, departmentList} = this.props;

        let departments = [], teams = [], members = [];

        if (typeof departmentList.departmentList !== 'undefined' && departmentList.departmentList !== null) {
            departmentList.departmentList.forEach(function(object) {
                departments.push({value: object.id, label:object.title});
            });
        }

        if (typeof teamList.teamList !== 'undefined' && teamList.teamList !== null) {
            teamList.teamList.forEach(function(object) {
                teams.push({value: object.id, label:object.name});
            });
        }

        if (typeof agentList.agentList !== 'undefined' && agentList.agentList !== null) {
            agentList.agentList.forEach(function(object) {
                let label = (<span>
                    <span className="chat-avatar" style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}}/>
                        {object.name}
                </span>
                );
                members.push({value: object.id, label:label});
            });
        }

        let project = this.props.projectData ? this.props.projectData : {};
        let currentMembers = project.members && project.members.length > 0 ? this.parseMembers(project.members) : {};

        return (<div className="sidebar-hover" style={{top: '152px'}}>

            <div className="sidebar-hover-content">
                <div className="sidebar-hover-header">
                    <i className="fa fa-tags"/> <span className="title"><span>Project -</span> {project.id ? 'Edit' : 'Create New'}</span>
                </div>
                <Formsy.Form onValid={this.enableButton} onInvalid={this.disableButton} onSubmit={this.props.createProject}>
                    <div className="sidebar-hover-content-box">
                        <FRC.Input name="projectId" type="hidden" value={project.id} />
                        <h2>Title</h2>
                        <FRC.Input name="title" type="text" placeholder="Title" validations="minLength:1" validationErrors={{minLength: "The title field is required"}} value={project.title} />
                        {this.serverValidation('title')}
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Departments</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            {departments ? <FRC.CheckboxGroupDeskPRO
                                name="departments"
                                label="Departments"
                                options={departments}
                                value={currentMembers['department'] ? currentMembers['department'] : []}
                                multiple
                                /> : ''}
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Teams</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            {teams ? <FRC.CheckboxGroupDeskPRO
                                name="teams"
                                label="Teams"
                                options={teams}
                                value={currentMembers['team'] ? currentMembers['team'] : []}
                                multiple
                                /> : ''}
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Project Members</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            {members ? <FRC.CheckboxGroupDeskPRO
                                name="members"
                                label="Members"
                                options={members}
                                value={currentMembers['person'] ? currentMembers['person'] : []}
                                multiple
                                /> : ''}
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <button type="submit" value="Save" className="button" disabled={!this.state.canSubmit}>Save</button>
                    </div>
                </Formsy.Form>
             </div>
        </div>
        );
    }
}
