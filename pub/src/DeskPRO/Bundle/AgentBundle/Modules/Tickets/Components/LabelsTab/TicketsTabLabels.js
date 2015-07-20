import React from "react";

export default class TicketsTabLabels extends React.Component {
  render() {
    const { labelsList } = this.props;
    
    const labels = labelsList.labels_list.map(letter => {
      const my_labels = letter.labels.map(label => (
        <a href="#" className="item-label">{label}</a>
      ));
      
      return (
        <div className="sidebar-label-list">
          <div className="letter">{letter.letter}</div>
          <div className="letter-labels">
            {my_labels}
          </div>
        </div>
      );
    });
    
    return (
      <div className="sidebar-list sidebar-list-labels" >
        {labels}

        <div className="list-sidebar-label-view">
          <a href="#" className="add-new">Create a new label</a>
        </div>
      </div>
    );
  }
}
