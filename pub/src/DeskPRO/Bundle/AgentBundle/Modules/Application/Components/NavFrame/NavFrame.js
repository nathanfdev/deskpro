import React from 'react';

export class NavFrame extends React.Component {
  render() {

    let outer, inner;
    this.props.children.forEach((child) => {
      if (child.props.part === 'outer') {
        outer = child;
      } else if (child.props.part === 'inner') {
        inner = child;
      }
    });

    return (
      <div>

        {outer}

        <section className="task-nav-frame dp-nav-frame">
          <div className="sidebar-wrapper" id="sidebar-wrapper">
            <a className="collapse-button" href="#"><i className="fa fa-angle-right"></i></a>
            <span className="collapse-controls">
              <span className="disc"></span>
              <span className="disc"></span>
              <i className="fa fa-caret-right"></i>
              <span className="disc"></span>
              <span className="disc"></span>
            </span>
            <aside className="sidebar has-tabs" id="sidebar">

              {inner}

            </aside>
          </div>
        </section>
      </div>
    );
  }
}
