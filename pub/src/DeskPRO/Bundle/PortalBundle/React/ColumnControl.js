import React from "react"
import _ from "lodash"

class SimpleCheckbox extends React.Component {
    onClick() {
        console.log('clicked ', this.props.data);
        this.props.toggleData(this.props.data);
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
            active: props.active
        }
    }
    toggleData(data) {
        let newActive = [];
        if ($.inArray(data, this.state.active) < 0) {
            newActive = this.state.active.filter((col) => {
                return col !== data;
            });
        } else {
            newActive.push(data);
        }
        this.setState({
            active: newActive,
            columns: this.state.columns
        });
        this.props.updateActiveCols(newActive);
    }
    render() {
        return (
            <div>
            <h1>Show Columns</h1>
            <ul>
                {this.state.columns.map((col) => {
                    return (<li key={col}><SimpleCheckbox data={col} label={col} active={$.inArray(col, this.state.active) >= 0} toggleData={this.toggleData.bind(this)}  /></li>);
                })}
            </ul>
          </div>
        );
    }
}
