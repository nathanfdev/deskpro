import React from "react"
import FilterControls from "DeskPRO/Bundle/PortalBundle/React/FilterControls"


export default class FeedbackFilter extends React.Component {
  render() {
    return (
      <article className="feedback-filter-interactive">

        <FilterControls filter_data={this.props.filter_data} />

        <div className="paged-results">


          <div className="feedback-item">


            <div className="feedback-item-controls">
              <a href="/feedback/view/asdf/vote-up" className="i-agree">
                <i className="fa fa-thumbs-up"></i>
                <div>I Agree</div>
                <span className="counter">-2</span>
              </a>
            </div>

            <div className="feedback-item-content">
              <span className="feedback-status">Planning</span>

              <h1>
                <span className="text-tag">Issue</span>
                <a href="/feedback/view/asdf">asdf</a>
              </h1>


              <div className="comment">
                <p>asdfasdfasdf</p>
              </div>

              <div className="feedback-item-meta">
                <ul>
                  <li><i className="fa fa-user"></i> goober</li>
                </ul>
              </div>
            </div>



          </div>



        </div>
      </article>
    );
  }
}
