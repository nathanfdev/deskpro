import React from 'react';

export class Pagination extends React.Component {

  render() {
    return (
      <div className="dpw--pagination">
        <ul className="pages-list">
          <li>
            <a href=""><i className="fa fa-caret-left"></i></a>
          </li>
          <li>
            <hr/>
          </li>
          <li>
            <a href="">1</a>
          </li>

          <li>
            <a href="" className="grey">2</a>
          </li>

          <li>
            <a href="" className="greyer">3</a>
          </li>
          <li>
            <span className="pagination-dots">&hellip;</span>
          </li>
          <li>
            <hr/>
          </li>
          <li>
            <a href=""><i className="fa fa-caret-right"></i></a>
          </li>
        </ul>
      </div>
    );
  }
}
