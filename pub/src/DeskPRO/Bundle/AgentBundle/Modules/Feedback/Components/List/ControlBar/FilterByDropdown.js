import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

const FilterByDropdown = React.createClass({

  propTypes: {
    filterOptions: PropTypes.array.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },

  mixins: [require('react-onclickoutside')],

  getInitialState() {
    return {
      category: { value: false },
      status: { value: { some: true } },
      custom_category: { value: false },
      date_created: { value: false }
    };
  },

  handleClickOutside() {
    this.props.toggleDropdown();
  },

  resetFilter(type) {
    this.setState({
      [type]: { value: false }
    });
  },

  render() {
    return (
      <Menu>
        <FilterItem
          filterType="category"
          isActive={Boolean(this.state.category.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Type"
          >
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="status"
          isActive={Boolean(this.state.status.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Status">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="custom_category"
          isActive={Boolean(this.state.custom_category.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Category">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="date_created"
          isActive={Boolean(this.state.date_created.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Date">
          <Menu>
            <div
              className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">

              <span className="dpw-navigation-dropdown-panel-close"><i className="fa fa-times"></i></span>

              <div className="dpw-date-picker">

                <div className="dpw-date-picker-panel-container">

                  <div className="dpw-date-picker-left">

                    <div className="dpw-date-picker-custom-date-and-time">
                      <div className="dpw-date-picker-custom-date">
                        <i className="fa fa-calendar-o"></i>
                        <input type="text" className="text-entry-date" value="08/29/2014"/>
                      </div>

                      <div className="dpw-date-picker-custom-time">
                        <i className="fa fa-clock-o"></i>

                        <div className="custom-time-dropdowns">
                          <span className="custom-time-dropdown">01 <i className="fa fa-caret-down"></i></span>
                          <span>:</span>
                          <span className="custom-time-dropdown">30 <i className="fa fa-caret-down"></i></span>
                          <span className="custom-time-dropdown">PM <i className="fa fa-caret-down"></i></span>
                        </div>

                      </div>
                    </div>

                    <div className="dpw-date-picker-calendar">
                      <table>
                        <thead>
                        <tr>
                          <td colSpan="7">
                            <div className="dpw-date-picker-calendar-jump">
                              <a href="#" className="month-jumper month-jumper-back"><i
                                className="fa fa-angle-left"></i></a>
                              <a href="#" className="year-jumper">August 2015 <i className="fa fa-caret-down"></i></a>
                              <a href="#" className="month-jumper month-jumper-forward"><i
                                className="fa fa-angle-right"></i></a>
                            </div>
                          </td>
                        </tr>

                        <tr>
                          <td>Mon</td>
                          <td>Tue</td>
                          <td>Thu</td>
                          <td>Wed</td>
                          <td>Fri</td>
                          <td>Sat</td>
                          <td>Sun</td>
                        </tr>
                        </thead>

                        <tr>
                          <td className="non-date">31</td>
                          <td><span className="date-title">1</span></td>
                          <td><span className="date-title">2</span></td>
                          <td><span className="date-title">3</span></td>
                          <td><span className="date-title">4</span></td>
                          <td><span className="date-title">5</span></td>
                          <td><span className="date-title">6</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">7</span></td>
                          <td><span className="date-title">8</span></td>
                          <td><span className="date-title">9</span></td>
                          <td><span className="date-title">10</span></td>
                          <td><span className="date-title">11</span></td>
                          <td><span className="date-title">12</span></td>
                          <td><span className="date-title">13</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">14</span></td>
                          <td><span className="date-title">15</span></td>
                          <td><span className="date-title">16</span></td>
                          <td><span className="date-title">17</span></td>
                          <td><span className="date-title">18</span></td>
                          <td><span className="date-title">19</span></td>
                          <td><span className="date-title">20</span></td>
                        </tr>

                        <tr>
                          <td>21</td>
                          <td className="selected-range selected-range-start"><span className="date-title">22</span>
                          </td>
                          <td className="selected-range"><span className="date-title">23</span></td>
                          <td className="selected-range"><span className="date-title">24</span></td>
                          <td className="selected-range"><span className="date-title">25</span></td>
                          <td className="selected-range"><span className="date-title">26</span></td>
                          <td className="selected-range range-row-end"><span className="date-title">27</span></td>
                        </tr>

                        <tr>
                          <td className="selected-range range-row-start"><span className="date-title">28</span></td>
                          <td className="selected-range"><span className="date-title">29</span></td>
                          <td className="selected-range range-row-end"><span className="date-title">30</span></td>
                          <td className="non-date">1</td>
                          <td className="non-date">2</td>
                          <td className="non-date">3</td>
                          <td className="non-date">4</td>
                        </tr>

                      </table>
                    </div>
                  </div>


                  <div className="dpw-date-picker-right">

                    <div className="dpw-date-picker-custom-date-and-time">
                      <div className="dpw-date-picker-custom-date">
                        <i className="fa fa-calendar-o"></i>
                        <input type="text" className="text-entry-date" value="08/29/2014"/>
                      </div>

                      <div className="dpw-date-picker-custom-time">
                        <i className="fa fa-clock-o"></i>

                        <div className="custom-time-dropdowns">
                          <span className="custom-time-dropdown">01 <i className="fa fa-caret-down"></i></span>
                          <span>:</span>
                          <span className="custom-time-dropdown">30 <i className="fa fa-caret-down"></i></span>
                          <span className="custom-time-dropdown">PM <i className="fa fa-caret-down"></i></span>
                        </div>

                      </div>
                    </div>
                    <div className="dpw-date-picker-calendar">
                      <table>
                        <thead>
                        <tr>
                          <td colSpan="7">
                            <div className="dpw-date-picker-calendar-jump">
                              <a href="#" className="month-jumper month-jumper-back"><i
                                className="fa fa-angle-left"></i></a>
                              <a href="#" className="year-jumper">August 2015 <i className="fa fa-caret-down"></i></a>
                              <a href="#" className="month-jumper month-jumper-forward"><i
                                className="fa fa-angle-right"></i></a>
                            </div>
                          </td>
                        </tr>

                        <tr>
                          <td>Mon</td>
                          <td>Tue</td>
                          <td>Thu</td>
                          <td>Wed</td>
                          <td>Fri</td>
                          <td>Sat</td>
                          <td>Sun</td>
                        </tr>
                        </thead>

                        <tr>
                          <td className="non-date">31</td>
                          <td className="selected-range range-row-start"><span className="date-title">1</span></td>
                          <td className="selected-range"><span className="date-title">2</span></td>
                          <td className="selected-range"><span className="date-title">3</span></td>
                          <td className="selected-range"><span className="date-title">4</span></td>
                          <td className="selected-range selected-range-end"><span className="date-title">5</span></td>
                          <td><span className="date-title">4</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">7</span></td>
                          <td><span className="date-title">8</span></td>
                          <td><span className="date-title">9</span></td>
                          <td><span className="date-title">10</span></td>
                          <td><span className="date-title">11</span></td>
                          <td><span className="date-title">12</span></td>
                          <td><span className="date-title">13</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">14</span></td>
                          <td><span className="date-title">15</span></td>
                          <td><span className="date-title">16</span></td>
                          <td><span className="date-title">17</span></td>
                          <td><span className="date-title">18</span></td>
                          <td><span className="date-title">19</span></td>
                          <td><span className="date-title">20</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">21</span></td>
                          <td><span className="date-title">22</span></td>
                          <td><span className="date-title">23</span></td>
                          <td><span className="date-title">24</span></td>
                          <td><span className="date-title">25</span></td>
                          <td><span className="date-title">26</span></td>
                          <td><span className="date-title">27</span></td>
                        </tr>

                        <tr>
                          <td><span className="date-title">28</span></td>
                          <td><span className="date-title">29</span></td>
                          <td><span className="date-title">30</span></td>
                          <td className="non-date"><span className="date-title">1</span></td>
                          <td className="non-date"><span className="date-title">2</span></td>
                          <td className="non-date"><span className="date-title">3</span></td>
                          <td className="non-date"><span className="date-title">4</span></td>
                        </tr>
                      </table>
                    </div>


                  </div>

                  <div className="dpw-date-picker-footer">
                    <a href="#" className="dpw--panel-button">Apply Date Range Filter</a>
                  </div>


                </div>


              </div>
            </div>
          </Menu>
        </FilterItem>
      </Menu>
    );
  }
});

module.exports.FilterByDropdown = FilterByDropdown;
