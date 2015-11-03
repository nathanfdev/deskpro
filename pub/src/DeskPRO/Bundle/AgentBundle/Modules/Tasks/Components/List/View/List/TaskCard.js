import React from 'react';
import { Card } from '../../../../../Common/Components/ListFrame/Card';

export class TaskCard extends React.Component {

  render() {
    return (
      <Card minimized={true} type="task">
        <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized">
          <span>Done</span>
          <i className="fa fa-check"/>
        </div>

        <div className="dpw--single-card-mark-done" onClick={this.toggleDone}>
          <i className="fa fa-check"/>
          <span>Mark Done</span>
        </div>

        <div className="dpw--card-line" onClick={this.expandDetails}>
          <div className="dpw--card-line-left card-title">
            <div className="dpwd--card-title strikethrough">
                <h1>Task title</h1> :
                <form className="inline-form">
                  <h1 className="ignore-react-onclickoutside">
                    <input type="text" name="title" value={'Task title'}/>
                  </h1>
                </form>
            </div>
          </div>

          <div className="dpw--card-line-right">
            <div className="dpw--card-expand">
              <a href="#">Detail button text <i className="fa fa-navicon"/></a>
            </div>
            <div className="dpwd--card-assigned">
              <div className="dpw--avatar-face" style={{position: 'relative'}}>
                <i className="fa fa-caret-down" />
              </div>
            </div>
          </div>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <span className="overdue dpwd--card-line-item">
              <i className="fa fa-calendar-o"/> Due: N/A
              <input type="text" name="due-date" className="due-date-field" disabled="disabled"/>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-book"/> Some project
              </span>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-link"/> <a href="#">Ticket title</a>
              </span>
            </span>
          </div>

          <div className="dpw--card-line-right">
            <span className="dpwd--card-line-item">
              0 <i className="fa fa-comment"/>
            </span>

            <span className="dpwd--card-line-item">
              <div>
                <span className="dpw--card-disc"/> 1/3 <i className="fa fa-folder-open"/>
              </div>
            </span>
          </div>
        </div>
      </Card>
    );
  }
}
