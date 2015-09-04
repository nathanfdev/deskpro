/**
 * Component to toggle view between the two modes: List and Table
 */
import React from 'react';
import * as actions from '../../Actions/ControlBarActions'
import $ from "jquery";

export class ListTableViewSwitcher extends React.Component {
    changeView(event) {
        event.stopPropagation();
        const {dispatch} = this.props;
        dispatch(actions.switchViewMode());
    }

    showViewModeChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            viewModeChoice = elem.closest('div.ticket-control-button').find('div.view-mode-choice');
        $('div.dropdown-choice').hide();
        viewModeChoice.show();
    }


    render() {
        const {viewMode, fields} = this.props;
        return (
            <div className="ticket-control-button">
                <span className="title">View:</span>
                <a href="#">
                    <span className="focus" onClick={this.showViewModeChoice.bind(this)}>{viewMode}</span>
                </a>
                <ListTableViewDropdown fields={fields} viewMode={viewMode} changeView={this.changeView.bind(this)}/>
            </div>
        );
    }
}

export class ListTableViewDropdown extends React.Component {

    closeDropdown(e) {
        event.preventDefault();
        event.stopPropagation();
        $(e.target).closest('.dropdown-choice').hide();
    }

    render() {
        const {viewMode, fields, changeView} = this.props;

        return (
            <div className="view-mode-choice dropdown-choice" style={{width:'300px'}}>
                <p>This dropdown is prototype only!</p>

                <div style={{textAlign:'right', width:'100%'}}>
                    <a href="#" onClick={this.closeDropdown.bind(this)}><span>X</span></a>
                </div>
                <div style={{width:'50%',float:'left'}}>
                    <label>
                        <input name="view-mode" type="radio" defaultChecked={viewMode === 'list'}
                               onChange={changeView.bind(this)}>
                            List View
                        </input>
                    </label>
                    <br/>
                    <fieldset>
                        <legend>Display fields</legend>
                        <DisplayFields fields={fields}/>
                    </fieldset>
                </div>
                <div style={{width:'50%',float:'left'}}>
                    <label>
                        <input name="view-mode" type="radio" defaultChecked={viewMode === 'table'}
                               onChange={changeView.bind(this)}>
                            Table View
                        </input>
                    </label>
                    <br/>
                    <fieldset>
                        <legend>Display fields</legend>
                        <DisplayFields fields={fields}/>
                    </fieldset>
                </div>
                <button>Save fields</button>
            </div>
        );
    }
}

export class DisplayFields extends React.Component {
    render() {
        const {fields} = this.props;
        return (
            <select multiple size="10">
                {fields.map((field, index) =>
                    <option key={index}>{field.label}</option>)}
            </select>
        );
    }
}