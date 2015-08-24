import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeader icon="fa-edit">Publish</NavFrameHeader>
        <TabsPane>
          <Tab title="KB">
            <div className="deskpro-app-major-buttons">
              <ul>
                <li>
                  <a href="#">
                    <span className="icon"><i className="fa fa-quote-left"></i></span>
                    <span className="title">Glossary</span>
                  </a>
                </li>
                <li>
                  <a href="#">
                    <span className="icon"><i className="fa fa-search"></i></span>
                    <span className="title">Search</span>
                  </a>
                </li>
                <li>
                  <a href="#">
                    <span className="icon"><i className="fa fa-comments-o"></i></span>
                    <span className="title">Comments</span>
                  </a>
                </li>
              </ul>
            </div>
          </Tab>
          <Tab title="News"></Tab>
          <Tab title="Downloads"></Tab>
          <Tab title="Todos"></Tab>
        </TabsPane>
      </NavFrame>
    );
  }


}
