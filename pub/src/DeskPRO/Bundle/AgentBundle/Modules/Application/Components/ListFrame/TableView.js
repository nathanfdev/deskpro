import React from 'react';

export class TableView extends React.Component {


  render() {
    return (
      <div className="tickets-tabular">
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

