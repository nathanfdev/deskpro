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
            canSubmit: false
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

    render() {
        const {agentList, teamList, departmentList} = this.props;

        return (<div className="sidebar-hover" style={{top: '152px'}}>

            <div className="sidebar-hover-content">
                <div className="sidebar-hover-header">
                    <i className="fa fa-tags"/> <span className="title"><span>Project -</span> Create New</span>
                </div>
                <Formsy.Form onValid={this.enableButton} onInvalid={this.disableButton}>
                    <div className="sidebar-hover-content-box">
                        <h2>Title</h2>
                        <FRC.Input name="title" type="text" placeholder="Title" required/>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Departments</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            <ul>{departmentList.departmentList ? departmentList.departmentList.map(function(object) {
                                return <li key={object.id}>
                                    <a href="#" className="checkbox-button">
                                        <span className="checkbox"><i className="fa fa-check"/></span><span
                                        className="name">{object.title}</span>
                                    </a>
                                </li>;
                            }) : ''}
                            </ul>
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Teams</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            <ul>{teamList.teamList ? teamList.teamList.map(function(object) {
                                return <li key={object.id}>
                                    <a href="#" className="checkbox-button">
                                        <span className="checkbox"><i className="fa fa-check"/></span><span
                                        className="name">{object.name}</span>
                                    </a>
                                </li>;
                            }) : ''}
                            </ul>
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <h2>Project Members</h2>
                        <div className="sidebar-hover-checkbox-collection">
                            <ul>{agentList.agentList ? agentList.agentList.map(function(object) {
                                return <li key={object.id}>
                                    <a href="#" className="checkbox-button">
                                        <span className="checkbox"><i className="fa fa-check"/></span> <span
                                        className="chat-avatar" style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}}/> <span
                                        className="name">{object.name}</span>
                                    </a>
                                </li>;
                            }) : ''}
                            </ul>
                        </div>
                    </div>

                    <div className="sidebar-hover-content-box">
                        <input type="submit" value="Save" className="button" disabled={!this.state.canSubmit} />
                    </div>
                </Formsy.Form>
             </div>
        </div>
        );
    }
}
