/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-08-02 10:16
 * Filename      : select.go
 * Description   :
 * *******************************************************/
package admin

type selected struct {
	Name   string //
	Setd   string
	Option map[string]string //
}

// &selected{}.SetSelect(name, option)
func (s *selected) SetSelect(name, setd string, option map[string]string) {
	s.Name = name
	s.Setd = setd
	s.Option = option
}

//func (s *selected) GetSelect() int {
//	if num, err := strconv.Atoa(this.Setd); err != nil {
//		return 30
//	} else {
//		return num
//	}
//}
