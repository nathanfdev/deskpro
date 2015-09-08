import React from 'react';

export class TableView extends React.Component {


  render() {
    return (
      <div className="dpmw--items-table-list">
        <table>
          {this.props.children}
        </table>
      </div>
    );
  }
}

export class TableBody extends React.Component {

  render() {

    return (
      <tbody>
      {this.props.children}
      </tbody>
    );
  }
}

