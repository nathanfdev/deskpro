import React from "react"
import _ from "lodash"

class SimpleCheckbox extends React.Component {
    onClick() {
        this.props.toggleColumnId(this.props.data);
    }
    render() {
        return (
            <div onClick={this.onClick.bind(this)} className="checkbox-container">
                <span className={"checkbox" + (this.props.active ? " checked" : "")}><i className="fa fa-check"></i></span>
                { this.props.label }
            </div>
        );
    }
}

export default class ColumnControl extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            columns: props.columns,
            active_ids: props.active_ids
        }
    }
    toggleColumnId(toggle_col_id) {
        let new_active_ids = [];
        if ($.inArray(toggle_col_id, this.state.active_ids) >= 0) {
            new_active_ids = this.state.active_ids.filter((col) => {
                return col != toggle_col_id;
            });
        } else {
            new_active_ids = this.state.active_ids;
            new_active_ids.push(toggle_col_id);
        }
        this.setState({
            active_ids: new_active_ids,
            columns: this.state.columns
        });
        this.props.updateActiveCols(new_active_ids);
    }
    render() {
        return (
            <div style={{"width": "100%", "height": "100%"}}>
                <h1>Show Columns</h1>
                <ul>
                    {this.state.columns.map((col) => {
                        return (<li key={col.id}><SimpleCheckbox data={col.id} label={col.label} active={$.inArray(col.id, this.state.active_ids) >= 0} toggleColumnId={this.toggleColumnId.bind(this)}  /></li>);
                    })}
                </ul>
          </div>
        );
    }
}
