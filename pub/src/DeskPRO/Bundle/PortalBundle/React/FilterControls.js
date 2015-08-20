import React from "react"


export default class FilterControls extends React.Component {
  constructor(props) {
    super(props);
    this.state = props.filter_data;
  }
  generateUrlFromState() {
    return '/feedback/browse/' + this.state.filter.status;
  }
  renderTabs() {
    let tabs = [];
    for (let i in this.state.available.status) {
      tabs.push(
        <li>
          <a href={'/feedback/browse/' + i}
             className={this.state.filter.status == i ? "active" : ""}
            >
            {this.state.available.status[i]}
            {/* TODO implement types <span><i className="fa fa-caret-down"></i></span>*/}
          </a>
        </li>
      );
    }

    return (
      <ul className="flat-tabs">
        <p>GENERATED URL: {this.generateUrlFromState()}</p>
        <p>STATE: {JSON.stringify(this.state)}</p>
        { tabs }
      </ul>
    );
  }
  render() {
    return (
      <div className="feedback-filter">

        {this.renderTabs()}

        <div className="table-meta">
          <div className="table-controls">
            <a href="#" className="column-control sort"><span>Sort</span></a>
            <a href="#" className="expand-control"><i className="fa fa-caret-right"></i></a>
          </div>
        </div>


        <div>
          <ul className="slider-list">


            <li>
              <div className="slider-panel">
                <a href="/feedback/browse/type-1" className="slider">
                  <span className="slider-status">on</span>
                  <span className="slider-icon"><i className="fa fa-check"></i></span>
                </a>
                <span className="slider-label">Suggestion</span>
                <span className="slider-options"><i className="fa fa-caret-down"></i></span>
              </div>
            </li>
            <li>
              <div className="slider-panel">
                <a href="/feedback/browse/type-4" className="slider">
                  <span className="slider-status">on</span>
                  <span className="slider-icon"><i className="fa fa-check"></i></span>
                </a>
                <span className="slider-label">Issue</span>
                <span className="slider-options"><i className="fa fa-caret-down"></i></span>
              </div>
            </li>
            <li>
              <div className="slider-panel">
                <a href="/feedback/browse/type-5" className="slider">
                  <span className="slider-status">on</span>
                  <span className="slider-icon"><i className="fa fa-check"></i></span>
                </a>
                <span className="slider-label">This is a sub of Issue</span>
                <span className="slider-options"><i className="fa fa-caret-down"></i></span>
              </div>
            </li>


          </ul>
        </div>
      </div>
    );
  }
}
